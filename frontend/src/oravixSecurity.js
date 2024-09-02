import _sodium from "libsodium-wrappers";

/**
 * @typedef {{header: {
 *      alg: string,
 *      typ: string
 *  },
 *  payload: {
 *      iss: string,
 *      sub: string,
 *      aud: string,
 *      exp: number,
 *      nbf: number,
 *      iat: number,
 *      jti: string},signature: string}} JWT
 *
 */
class OravixSecurity {
    #sodium;
    #keypair;
    #url;
    #winId;
    #isConstructed = false;
    #isProcessSecure = true;

    constructor(url) {
        this.#url = url;
        (async () => {
            await _sodium.ready;
            this.#sodium = _sodium;
            if (getCookies(document.cookie)["PHPSESSID"] === undefined) {
                let keypair = this.#sodium.crypto_box_keypair();
                await fetch(url + "/encryption", {
                    method: "POST",
                    body: this.#sodium.to_base64(keypair.publicKey, this.#sodium.base64_variants.ORIGINAL)
                })
                    .then(async res => res.text())
                    .then(async res => {
                        this.#keypair = {
                            publicKey: this.#sodium.from_base64(res, this.#sodium.base64_variants.ORIGINAL),
                            privateKey: keypair.privateKey,
                            keyType: "x25519"
                        }
                        localStorage.setItem("public-key", this.#sodium.to_base64(this.#keypair.publicKey, this.#sodium.base64_variants.ORIGINAL));
                        localStorage.setItem("private-key", this.#sodium.to_base64(this.#keypair.privateKey, this.#sodium.base64_variants.ORIGINAL))
                    });

            } else {
                this.#keypair = {
                    publicKey: this.#sodium.from_base64(localStorage.getItem("public-key"), this.#sodium.base64_variants.ORIGINAL),
                    privateKey: this.#sodium.from_base64(localStorage.getItem("private-key"), this.#sodium.base64_variants.ORIGINAL),
                    keyType: "x25519"
                }
            }

            if (sessionStorage.getItem("win-id") === null) {
                await fetch(url + "/window-id", {
                    method: "GET"
                })
                    .then(async res => {
                        this.#winId = await res.headers.get("win-id");
                    });
                sessionStorage.setItem("win-id", this.#winId);
            }
            this.#winId = sessionStorage.getItem("win-id");
            let searchParams = new URL(document.location.toString()).searchParams;
            if (searchParams.has("nonce")) {
                let nonce = this
                    .#sodium
                    .from_base64(searchParams.get("nonce"), this.#sodium.base64_variants.ORIGINAL);
                if (searchParams.has("access")) {
                    localStorage
                        .setItem("access-token", this.#getBeforeSearchParameter("access", searchParams, nonce));
                    localStorage
                        .setItem("refresh-token", this.#getBeforeSearchParameter("refresh", searchParams, nonce));
                }
                if (searchParams.has("win-id")) {
                    let winId = this.#getBeforeSearchParameter("win-id", searchParams, nonce);
                    if (searchParams.has("redirect-url")) {
                        localStorage.setItem(winId + "-redirect-url", this.#getBeforeSearchParameter("redirect-url", searchParams, nonce));
                    }
                }
            }
            if (localStorage.getItem("access-token") !== null) {
                if (await this.isSecure()) {
                    const data = this.getJsonData();
                    setTimeout(async () => {
                        this.#isProcessSecure = false;
                        if (!(await this.#refreshTokens())) {
                            this.logout();
                        }
                        this.#isProcessSecure = true;
                    }, (data.accessToken.payload.iat + data.accessToken.payload.exp) * 1000 - Date.now());
                } else {
                    this.logout();
                }
            }
            this.#isConstructed = true;
        })()
    }

    #getBeforeSearchParameter(name, searchParams, nonce) {
        return this.#sodium.to_string(this
            .#sodium
            .crypto_box_open_easy(this.#sodium.from_base64(searchParams
                .get(name), this.#sodium.base64_variants.ORIGINAL), nonce, this.#keypair.publicKey, this.#keypair.privateKey))
    }

    async getSearchParameter(name, searchParams, nonce) {
        await until(_ => this.#isConstructed);
        return this.#sodium.to_string(this
            .#sodium
            .crypto_box_open_easy(this.#sodium.from_base64(searchParams
                .get(name), this.#sodium.base64_variants.ORIGINAL), nonce, this.#keypair.publicKey, this.#keypair.privateKey))
    }

    async #secureFetch(input, init) {
        await until(_ => this.#isConstructed && this.#isProcessSecure);
        init.headers = {
            "authorization": "Bearer " + localStorage.getItem("access-token"), "win-id": this.#winId, ...init.headers
        }
        return await fetch(input, init);
    }

    /**
     *
     * @param input {RequestInfo | URL}
     * @param init {RequestInit}
     * @returns {Promise<{headers: Headers, status: number, body: string}>}
     */
    async secureEncryptedFetch(input, init) {
        await until(_ => this.#isConstructed);
        return await this.#encrypted(input, init, true);
    }

    /**
     *
     * @param input {RequestInfo | URL}
     * @param init {RequestInit}
     * @returns {Promise<{headers: Headers, status: number, body: string}>}
     */
    async encryptedFetch(input, init) {
        return await this.#encrypted(input, init, false);
    }

    async noCryptFetch(input, init) {
        await until(_ => this.#isConstructed);
        const newInput = new URL(input);
        newInput.searchParams
            .set("encrypted", "0");
        init.headers = {
            "win-id": this.#winId, ...init.headers
        }
        return await fetch(newInput, init);
    }

    async noCryptSecureFetch(input, init) {
        const newInput = new URL(input);
        newInput.searchParams
            .set("encrypted", "0");
        return await this.#secureFetch(newInput, init);
    }

    async #encrypted(input, init, secure) {
        await until(_ => this.#isConstructed);
        let nonce = this.#sodium.randombytes_buf(this.#sodium.crypto_secretbox_NONCEBYTES);
        const newInput = new URL(input);
        for (const param of newInput.searchParams.keys()) {
            newInput.searchParams.set(param, this
                .#sodium
                .to_base64(this
                    .#sodium
                    .crypto_box_easy(newInput
                        .searchParams
                        .get(param), nonce, this.#keypair.publicKey, this.#keypair.privateKey), this.#sodium.base64_variants.ORIGINAL))
        }
        newInput.searchParams.set("encryption", "1");
        init.body = (init.body !== undefined ? this
            .#sodium
            .to_base64(this.#sodium.crypto_box_easy(init.body, nonce, this.#keypair.publicKey, this.#keypair.privateKey), this.#sodium.base64_variants.ORIGINAL) : undefined);
        init.headers = {"nonce": this.#sodium.to_base64(nonce, this.#sodium.base64_variants.ORIGINAL), ...init.headers};
        init.headers = {"win-id": this.#winId, ...init.headers};
        const res = secure ? await this.#secureFetch(newInput, init) : await this.noCryptFetch(newInput, init);
        const text = await res.text();
        let decrypted = text;
        try {
            decrypted = new TextDecoder().decode(this.#sodium.crypto_box_open_easy(this.#sodium.from_base64(text, this.#sodium.base64_variants.ORIGINAL), this.#sodium.from_base64(res.headers.get("nonce"), this.#sodium.base64_variants.ORIGINAL), this.#keypair.publicKey, this.#keypair.privateKey))
        } catch (e) {
            decrypted = "{}";
        }
        return {
            headers: await res.headers,
            status: await res.status,
            body: decrypted
        }
    }


    async login(username, password, redirect = window.location.toString()) {
        const url = this.#url;
        let status;
        await this.encryptedFetch(`${url}/login?redirect-url=${encodeURIComponent(redirect)}`, {
            method: "POST", body: JSON.stringify({
                username: username, password: password
            }), headers: {
                "content-type": "application/json"
            }
        })
            .then(res => {
                status = res.status;
            });
        return status;
    }

    logout() {
        const url = this.#url;
        this.encryptedFetch(`${url}/logout`, {
            method: "POST", body: JSON.stringify({
                access: localStorage.getItem("access-token"), refresh: localStorage.getItem("refresh-token")
            }), headers: {
                "content-type": "application/json"
            }
        }).then(async res => {
            localStorage.removeItem("access-token");
            localStorage.removeItem("refresh-token");
            window.location.reload();
        })
    }

    changePassword() {

    }

    changeEmail(newEmail) {

    }

    async register(username, password, firstName, lastName, email, redirect = window.location.toString()) {
        const url = this.#url;
        let message;
        await this.encryptedFetch(`${url}/register?redirect-url=${encodeURIComponent(redirect)}`, {
            method: "POST", body: JSON.stringify({
                username: username, password: password, first_name: firstName, last_name: lastName, email: email
            }), headers: {
                "content-type": "application/json"
            }
        })
            .then(res => {
                message = res.body;
            });
        return message;
    }

    isSecure() {
        return !this.#isAccessTokenExpired() && localStorage.getItem("access-token") !== null
    }

    async #refreshTokens() {
        let response;
        this
            .encryptedFetch(this.#url + "/refresh-token", {
                method: "POST", headers: {
                    "content-type": "application/json"
                }, body: {
                    accessToken: localStorage.getItem("access-token"),
                    refreshToken: localStorage.getItem("refresh-token")
                }
            })
            .then(async res => response = await res)
        if (await response?.status === 403) {
            return false;
        } else {
            const data = JSON.parse(await response?.body);
            localStorage.setItem("access-token", data.access);
            localStorage.setItem("refresh-token", data.refresh);
            const jsonData = this.getJsonData();
            setTimeout(async () => {
                this.#isProcessSecure = false;
                if (!(await this.#refreshTokens())) {
                    this.logout();
                }
                this.#isProcessSecure = true;
            }, (jsonData.accessToken.payload.iat + jsonData.accessToken.payload.exp) * 1000 - Date.now());
            return true;
        }
    }

    #isAccessTokenExpired() {
        const data = this.getJsonData();
        return (data?.accessToken?.payload.iat + data?.accessToken?.payload.exp) * 1000 < Date.now() || data === null;
    }

    #isRefreshTokenExpired() {
        const data = this.getJsonData();
        return (data?.refreshToken?.payload.iat + data?.refreshToken?.payload.exp) * 1000 < Date.now() || data === null;
    }

    /**
     * @return {{
     * accessToken: JWT,
     * refreshToken: JWT} | null}
     */
    getJsonData() {
        try {
            const access = localStorage.getItem("access-token").split(".");
            const refresh = localStorage.getItem("refresh-token").split(".");
            return {
                accessToken: {
                    header: JSON.parse(atob(access[0])),
                    payload: JSON.parse(atob(access[1])),
                    signature: atob(access[2])
                }, refreshToken: {
                    header: JSON.parse(atob(refresh[0])),
                    payload: JSON.parse(atob(refresh[1])),
                    signature: atob(refresh[2])
                },
            }
        } catch (e) {
            return null;
        }
    }
}

window.addEventListener("storage", (event) => {
    const redirect = getFromLocalStorage("redirect-url");
    if (redirect !== null) {
        removeFromLocalStorage("redirect-url");
        window.location.assign(redirect);
    }
});

function getFromLocalStorage(name) {
    return localStorage.getItem(`${sessionStorage.getItem("win-id")}-${name}`);
}

function addToLocalStorage(name, value) {
    localStorage.setItem(`${sessionStorage.getItem("win-id")}-${name}`, value);
}

function removeFromLocalStorage(name) {
    localStorage.removeItem(`${sessionStorage.getItem("win-id")}-${name}`);
}

const getCookies = (cookieStr) => cookieStr.split(";")
    .map(str => str.trim().split(/=(.+)/))
    .reduce((acc, curr) => {
        acc[curr[0]] = curr[1];
        return acc;
    }, {})

function until(conditionFunction) {

    const poll = resolve => {
        if ((() => {
            try {
                return conditionFunction()
            } catch (e) {
                return false
            }
        })()) resolve(); else setTimeout(_ => poll(resolve), 400);
    }

    return new Promise(poll);
}

export default OravixSecurity;
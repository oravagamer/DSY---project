import {create} from "zustand";
import {backendUrl} from "../../settings.js";
import {createJSONStorage, persist} from "zustand/middleware";

const useAuthDataStore = create(
    persist(
        (set, get) => ({
            accessToken: "",
            refreshToken: "",
            isLoggedIn: () => {
                return get().accessToken !== "";
            },
            login: async (username, password) => {
                const res = await fetch(`${backendUrl}/login.php`, {
                    method: "POST",
                    body: JSON.stringify({username: username, password: password}),
                    headers: {
                        "Content-Type": "application/json"
                    }
                });
                const resData = await res.json();
                set({accessToken: await resData.access, refreshToken: await resData.refresh});
                return res;
            },
            refreshJWT: async () => {
                const res = await fetch(`${backendUrl}/refresh_token.php`, {
                    method: "POST",
                    body: JSON.stringify({access: get().accessToken, refresh: get().refreshToken}),
                    headers: {
                        "Content-Type": "application/json"
                    }
                })
                if (await res.status === 403) {
                    get().logout();
                }
                const resData = await res.json();
                set({accessToken: await resData.access, refreshToken: await resData.refresh});
            },
            logout: async () => {
                const res = await fetch(`${backendUrl}/logout.php`, {
                    method: "POST",
                    body: JSON.stringify({access: get().accessToken, refresh: get().refreshToken}),
                    headers: {
                        "Content-Type": "application/json"
                    }
                });
                set({accessToken: "", refreshToken: ""});
            },
            isNotExpired: () => {
                try {
                    const accPayload = get().getJSONData().accessToken.payload;
                    return Math.floor(Date.now() / 1000) < accPayload.exp && get().refreshTokenIsNotExpired();
                } catch (error) {
                    return false;
                }
            },
            refreshTokenIsNotExpired: () => {
                try {
                    const refPayload = get().getJSONData().refreshToken.payload;
                    return Math.floor(Date.now() / 1000) < refPayload.exp;
                } catch (error) {
                    return false;
                }
            },
            getJSONData: () => {
                try {
                    const accSplit = get().accessToken.split(".");
                    const refSplit = get().refreshToken.split(".");
                    return {
                        accessToken: {
                            header: JSON.parse(atob(accSplit[0])),
                            payload: JSON.parse(atob(accSplit[1]))
                        },
                        refreshToken: {
                            header: JSON.parse(atob(refSplit[0])),
                            payload: JSON.parse(atob(refSplit[1]))
                        }
                    }
                } catch (error) {
                    return [];
                }
            }
        }),
        {
            name: "auth-storage",
            storage: createJSONStorage(() => sessionStorage)
        }
    )
);

export default useAuthDataStore;
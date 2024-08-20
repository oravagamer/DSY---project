import {useEffect, useState} from "react";
import oravixSecurity from "../security.js";

/**
 *
 * @param input {RequestInfo | URL}
 * @param init {RequestInit}
 * @param secure
 * @param encrypted
 * @return {[{responseData: JSON, responseStatus: number, loading: boolean},refetch: () => Promise<void>]}
 */
const useFetch = (input, init, secure = false, encrypted = true) => {
    const [responseData, setResponseData] = useState(null);
    const [responseStatus, setResponseStatus] = useState(undefined);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        refetch();
    }, []);

    const refetch = async () => {
        setLoading(true);
        if (secure && encrypted) {
            oravixSecurity
                .secureEncryptedFetch(input, init)
                .then(async res => {
                    setResponseStatus(res.status);
                    setResponseData(JSON.parse(res.body));
                });
        } else if (!secure && encrypted) {
            oravixSecurity
                .encryptedFetch(input, init)
                .then(async res => {
                    setResponseStatus(res.status);
                    setResponseData(JSON.parse(res.body));
                })
        } else if (secure && !encrypted) {
            oravixSecurity
                .noCryptSecureFetch(input, init)
                .then(async res => {
                    setResponseStatus(res.status);
                    setResponseData(await res.json());
                })
        } else {
            oravixSecurity
                .noCryptFetch(input, init)
                .then(async res => {
                    setResponseStatus(res.status);
                    setResponseData(await res.json());
                })
        }
        setLoading(false);

    }

    return [{data, responseStatus, loading}, refetch];

}

export default useFetch;
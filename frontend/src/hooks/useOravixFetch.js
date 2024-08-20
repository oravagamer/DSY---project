import {useEffect, useState} from "react";
import useOravixSecurity from "./useOravixSecurity.js";

/**
 *
 * @param input {RequestInfo | URL}
 * @param init {RequestInit}
 * @param secure
 * @param encrypted
 * @param initialValue {any}
 * @return {{data: JSON, status: number, loading: boolean, refetch: () => void}}
 */
const useOravixFetch = (input, init, secure = false, encrypted = true, initialValue = undefined) => {
    const [data, setData] = useState(initialValue);
    const [status, setStatus] = useState(undefined);
    const [loading, setLoading] = useState(true);
    const {security} = useOravixSecurity();
    const [refetch, setRefetch] = useState(false);

    useEffect(() => {
        setLoading(true);
        if (secure && encrypted) {
            security
                .secureEncryptedFetch(input, init)
                .then(async res => {
                    setData(JSON.parse(res.body));
                    setStatus(res.status);
                })
                .finally(() => setLoading(false));
        } else if (secure && !encrypted) {
            security
                .noCryptSecureFetch(input, init)
                .then(async res => {
                    setData(await res.json());
                    setStatus(await res.status);
                })
                .finally(() => setLoading(false));
        } else if (!secure && encrypted) {
            security
                .encryptedFetch(input, init)
                .then(async res => {
                    setData(JSON.parse(res.body));
                    setStatus(res.status);
                })
                .finally(() => setLoading(false));
        } else {
            security
                .noCryptFetch(input, init)
                .then(async res => {
                    setData(await res.json());
                    setStatus(await res.status);
                })
                .finally(() => setLoading(false));
        }
    }, [refetch]);

    return {data, status, loading, refetch: () => setRefetch(!refetch)};
}

export default useOravixFetch;
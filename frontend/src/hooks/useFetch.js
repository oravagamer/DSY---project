import {useEffect, useState} from "react";

const useFetch = (input, init) => {
    const [responseData, setResponseData] = useState(null);
    const [responseStatus, setResponseStatus] = useState(undefined);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        (async () => {
            try {
                const response = await fetch(input, init);
                setResponseStatus(await response.status);
                try {
                    setResponseData(await response.json());
                } catch (err) {
                    setResponseData(await response.text());
                }
            } catch (err) {
                setError(await err);
            } finally {
                setLoading(false);
            }

        })();

    }, [loading]);

    const refetch = () => {
        setLoading(true);
    }

    return [{responseData, responseStatus, error, loading}, refetch];

}

export default useFetch;
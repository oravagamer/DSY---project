import {useEffect, useState} from "react";
import customFetch from "../functions/customFetch.js";

const useFetch = (input, init) => {
    const [responseData, setResponseData] = useState(null);
    const [responseStatus, setResponseStatus] = useState(undefined);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        refetch();
    }, []);

    const refetch = async () => {
        setLoading(true);
        const data = await customFetch(input, init);
        setResponseStatus(await data.response.status);
        try {
            setResponseData(await data.response.json());
        } catch (err) {
            setResponseData(await data.response.text());
        }
        setError(await data.error);

        await setLoading(false);

    }

    return [{responseData, responseStatus, error, loading}, refetch];

}

export default useFetch;
import {useEffect} from "react";

const NotFound = () => {
    useEffect(() => {
        throw new Response("Not found", {status: 404, statusText: "Not found"})
    }, []);
    return <></>;
}

export default NotFound;
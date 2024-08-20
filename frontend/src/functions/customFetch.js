import {toast} from "react-toastify";

const customFetch = async (input, init) => {
    let localResponse;
    let localError;
    await fetch(input, init)
        .then(async response => {
            localResponse = await response.clone();
            const myResponse = await response.clone();
            if ((await myResponse.status).toString()[0] !== "2") {
                toast(await myResponse.status, {
                    type: await myResponse.status < 200 ? "info" : await myResponse.status < 400 ? "warning" : "error"
                });
            }
        })
        .catch(async error => {
            localError = await error;
        });

    return {
        response: await localResponse,
        error: await localError
    }
}

export default customFetch;
import {useContext} from 'react';
import {oravixContext} from "../functions/createOravixAuthContext.js";

const useOravixSecurity = () => {
    const context = useContext(oravixContext);

    const getUserId = () => {
        const data = context.oravixSecurity.getJsonData();
        return data === null ? undefined : data.accessToken.payload.sub;
    }

    const getWinId = () => {
        return sessionStorage.getItem("win-id")
    }

    return {security: context.oravixSecurity, getUserId: getUserId, winId: getWinId};

}

export default useOravixSecurity;
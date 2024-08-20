import {useContext} from 'react';
import {oravixContext} from "../functions/createOravixAuthContext.js";

const useOravixSecurity = () => {
    const context = useContext(oravixContext);

    const getUserId = () => {
        const data = context.oravixSecurity.getJsonData();
        return data === null ? undefined : data.accessToken.payload.sub;
    }

    return {security: context.oravixSecurity, getUserId: getUserId};

}

export default useOravixSecurity;
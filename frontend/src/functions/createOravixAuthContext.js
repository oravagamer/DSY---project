import {createContext} from 'react';

export const createOravixAuthContext = (initialContext) => {
    return createContext({
        ...initialContext
    })
}

export default createOravixAuthContext;
export const oravixContext = createOravixAuthContext();
export const oravixContextConsumer = oravixContext.Consumer;
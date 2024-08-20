import {oravixContext as AuthContext} from "../functions/createOravixAuthContext.js";

const Security = ({
                      children,
                      oravixClass
                  }) => {
    return (<AuthContext.Provider value={{oravixSecurity: oravixClass}}>{children}</AuthContext.Provider>)
}

export default Security;
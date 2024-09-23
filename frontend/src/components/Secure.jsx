import {Navigate} from 'react-router-dom'
import {useEffect, useState} from "react";
import {useLocation} from 'react-router-dom';
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const Secure = ({redirect = false, children}) => {
    const [isSecure, setIsSecure] = useState(true);
    let location = useLocation();
    const {security} = useOravixSecurity();

    useEffect(() => {
        setIsSecure(security
            .isSecure())
    }, [isSecure, location]);

    if (isSecure) {
        return (<>{children}</>)
    } else if (redirect) {
        return (<Navigate to="/login" />);
    }
}

export default Secure;
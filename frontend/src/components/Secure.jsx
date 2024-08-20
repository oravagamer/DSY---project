import {Navigate} from 'react-router-dom'
import oravixSecurity from "../security.js";
import {useEffect, useState} from "react";
import {useLocation} from 'react-router-dom';

const Secure = ({redirect = false, children}) => {
    const [isSecure, setIsSecure] = useState(true);
    let location = useLocation();

    useEffect(() => {
        setIsSecure(oravixSecurity
            .isSecure())
    }, [isSecure, location]);

    if (isSecure) {
        return (<>{children}</>)
    } else if (redirect) {
        return (<Navigate to="/login" />);
    }
}

export default Secure;
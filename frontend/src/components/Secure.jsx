import useAuthDataStore from "../store/authDataStore.js";
import {Navigate, useLocation} from 'react-router-dom'
import {useEffect} from "react";

const Secure = (props) => {
    const auth = useAuthDataStore();
    const location = useLocation();

    useEffect(() => {
    }, [location]);

    if (auth.isNotExpired()) {
        return (<>{props.children}</>)
    } else if (auth.refreshTokenIsNotExpired()) {
        auth.refreshJWT();
        return (<>{props.children}</>)
    } else {
        return (<Navigate to="/" />);
    }
}

export default Secure;
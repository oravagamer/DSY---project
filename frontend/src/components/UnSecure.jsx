import {Navigate} from "react-router-dom";
import oravixSecurity from "../security.js";
import {useEffect, useState} from "react"

const UnSecure = (props) => {
    const [isSecure, setIsSecure] = useState(false);

    useEffect(() => {
        setIsSecure(oravixSecurity
            .isSecure())
    }, [isSecure]);

    if (isSecure) {
        return (<Navigate to="/dash/home" />)
    } else {
        return (<>{props.children}</>)
    }
}

export default UnSecure;
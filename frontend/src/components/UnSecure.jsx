import {Navigate} from "react-router-dom";
import {useEffect, useState} from "react"
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const UnSecure = (props) => {
    const [isSecure, setIsSecure] = useState(false);
    const {security} = useOravixSecurity();

    useEffect(() => {
        setIsSecure(security
            .isSecure())
    }, [isSecure]);

    if (isSecure) {
        return (<Navigate to="/dash/home" />)
    } else {
        return (<>{props.children}</>)
    }
}

export default UnSecure;
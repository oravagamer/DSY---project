import useAuthDataStore from "../store/authDataStore.js";
import {Navigate} from "react-router-dom";

const UnSecure = (props) => {
    const auth = useAuthDataStore();
    if (auth.isNotExpired()) {
        return (<Navigate to="/dash"/>)
    } else {
        return (<>{props.children}</>)
    }
}

export default UnSecure;
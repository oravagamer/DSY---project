import useAuthDataStore from "../store/authDataStore.js";
import {Navigate} from "react-router-dom";

const RoleRestricted = (props) => {
    const auth = useAuthDataStore();
    if (auth.getJSONData().accessToken.payload.roles.indexOf(props.role) !== -1) {
        return (<>{props.children}</>)
    } else {
        return (<Navigate to="/dash/home" />)
    }
}

export default RoleRestricted;
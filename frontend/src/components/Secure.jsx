import useAuthDataStore from "../store/authDataStore.js";
import {Navigate} from 'react-router-dom'

const Secure = (props) => {
    const auth = useAuthDataStore();

    if (auth.isNotExpired()) {
        return (<>{props.children}</>)
    } else if (auth.refreshTokenIsNotExpired()) {
        auth.refreshJWT();
        return (<>Secure{props.children}</>)
    } else {
        return (<Navigate to="/" />);
    }
}

export default Secure;
import useAuthDataStore from "../store/authDataStore.js";
import {Navigate} from 'react-router-dom'

const Secure = (props) => {
    const auth = useAuthDataStore();

    if (auth.isNotExpired()) {
        return (<div>Secure{props.children}<input type="button" onClick={auth.logout} placeholder="test" /></div>)
    } else if (auth.refreshTokenIsNotExpired()) {
        auth.refreshJWT();
        return (<div>Secure{props.children}</div>)
    } else {
        return (<Navigate to="/" />);
    }
}

export default Secure;
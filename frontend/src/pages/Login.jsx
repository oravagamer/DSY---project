import {useRef} from "react";

const getLoginData = async (loginURL, data) => {
    const res = await fetch(loginURL, {
        method: "POST",
        body: JSON.stringify(data)
    })
}

const Login = () => {
    const email = useRef("");
    const password = useRef("");

    const login = () => {
        let data = {
            email: email.current.value,
            password: password.current.value
        };
        console.log(data);
    }

    return (<form>
        <input type="text" placeholder="Username" ref={email} />
        <input type="password" placeholder="Password" ref={password} />
        <input type="button" value="Login" onClick={login}/>
    </form>)
}

export default Login;
import {useRef} from "react";
import "./Login.css";

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

    return (<div id="background">
        <header id="header"></header>
        <section id="login">
            <form id="login-form">
                <div id="label">Login</div>
                <input id="username" type="text" placeholder="Username" ref={email}/>
                <input id="password" type="password" placeholder="Password" ref={password}/>
                <input id="button" type="button" value="Submit" onClick={login}/>
            </form>
        </section>
    </div>)
}

export default Login;
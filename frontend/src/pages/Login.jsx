import {useRef} from "react";
import styles from './Login.module.scss';

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

    return (<div className={styles["login-background"]}>
        <header className={styles["login-header"]}></header>
        <section className={styles["login-section"]}>
            <form className={styles["login-form"]}>
                <div className={styles["login-label"]}>Login</div>
                <input className={styles["login-username"]} type="text" placeholder="Username" ref={email}/>
                <input className={styles["login-password"]} type="password" placeholder="Password" ref={password}/>
                <input className={styles["login-button"]} type="button" value="Submit" onClick={login}/>
            </form>
        </section>
    </div>)
}

export default Login;
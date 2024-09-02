import {useEffect, useState} from "react";
import oravixSecurity from "../security.js";
import {Link, useNavigate} from "react-router-dom";
import {Typography, TextField, Button, CardContent, CardActions, Card} from "@mui/material";
import PasswordInput from "../components/PasswordInput.jsx";
import Redirect from "./Redirect.jsx";

const Login = () => {
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");
    const [passwordValid, setPasswordValid] = useState("");
    const [usernameValid, setUsernameValid] = useState("");
    const [usernameFocus, setUsernameFocus] = useState(false);
    const [logged, setLoggeded] = useState(false);
    const login = event => {
        event.preventDefault();
        oravixSecurity
            .login(username, password)
            .then(async res => {
                if (await res === 403) {
                    setPasswordValid("Data does not match");
                    setUsernameValid("Data does not match");
                } else {
                    setLoggeded(true);
                }
            })
    }

    useEffect(() => {
    }, []);

    return (logged ? <Redirect /> : (<>
        <Card
            sx={{width: "350px", height: "max-content", alignSelf: "center"}}
            component="form"
            action="#"
            method="POST"
            onSubmit={login}>
            <CardContent align="center" sx={{paddingBottom: "0!important"}}>
                <Typography gutterBottom variant="h3" component="div">Login</Typography>
                <TextField
                    required
                    variant="filled"
                    label={usernameValid !== "" ? usernameValid : "Username"}
                    color={usernameValid !== "" ? "error" : "primary"}
                    value={username}
                    onChange={(event) => {
                        setUsername(event.target.value);
                        setUsernameFocus(false);
                        setUsernameValid("");
                    }}
                    sx={{mb: 1.5, width: '25ch'}}
                    inputProps={{minLength: 3}}
                    focused={usernameFocus || usernameValid !== ""}
                    onFocus={() => setUsernameFocus(true)}
                    onBlur={() => setUsernameFocus(false)}
                    onInvalid={event => {
                        event.preventDefault();
                        setUsernameValid(event.target.value.length < 3 ? "Min 3 chars" : "");
                    }}
                    autoComplete="username email" />
                <PasswordInput
                    password={password}
                    setPassword={setPassword}
                    autoComplete="current-password"
                    message={passwordValid}
                    sx={{m: 1, width: '25ch'}} />
            </CardContent>
            <CardActions align="center" display="flex" sx={{paddingY: "20px", justifyContent: "space-around"}}>
                <Button component={Link} to="/register">Register</Button>
                <Button variant="contained" type="submit">Login</Button>
            </CardActions>
        </Card>
    </>))
}

export default Login;
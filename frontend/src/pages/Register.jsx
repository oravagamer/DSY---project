import {useState} from "react";
import {Link} from "react-router-dom";
import Card from '@mui/material/Card';
import CardActions from '@mui/material/CardActions';
import CardContent from '@mui/material/CardContent';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Typography from "@mui/material/Typography";
import PasswordInput from "../components/PasswordInput.jsx";
import {frontendUrl} from "../../settings.js";
import EmailWait from "./EmailWait.jsx";
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const Register = () => {
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");
    const [firstName, setFirstName] = useState("");
    const [lastName, setLastName] = useState("");
    const [email, setEmail] = useState("");
    const [usernameValid, setUsernameValid] = useState("");
    const [firstNameValid, setFirstNameValid] = useState("");
    const [lastNameValid, setLastNameValid] = useState("");
    const [emailValid, setEmailValid] = useState("");
    const [usernameFocus, setUsernameFocus] = useState(false);
    const [firstNameFocus, setFirstNameFocus] = useState(false);
    const [lastNameFocus, setLastNameFocus] = useState(false);
    const [emailFocus, setEmailFocus] = useState(false);
    const [registered, setRegistered] = useState(false);
    const {security} = useOravixSecurity();

    const register = event => {
        event.preventDefault();
        security
            .register(username, password, firstName, lastName, email, frontendUrl + "/login")
            .then(async res => {
                let value = await res;
                if (value.includes("for key 'username'")) {
                    setUsernameValid("Username already used");
                } else if (value.includes("for key 'email'")) {
                    setEmailValid("Email already used");
                } else {
                    setRegistered(true)
                }
            });
    }

    return (registered ? <EmailWait /> : (<>
            <Card sx={{width: "350px", height: "max-content", alignSelf: "center", borderRadius: "5px"}} action="#"
                  method="POST" component="form"
                  onSubmit={register}>
                <CardContent align="center" sx={{paddingBottom: "0!important"}}>
                    <Typography gutterBottom variant="h3" component="div">Register</Typography>
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
                        sx={{mb: 1.5}}
                        inputProps={{minLength: 3}}
                        focused={usernameFocus || usernameValid !== ""}
                        onFocus={() => setUsernameFocus(true)}
                        onBlur={() => setUsernameFocus(false)}
                        onInvalid={event => {
                            event.preventDefault();
                            setUsernameValid(event.target.value.length < 3 ? "Min 3 chars" : "");
                        }}
                        autoComplete="username" />
                    <PasswordInput password={password} setPassword={setPassword} autoComplete="new-password"
                                   required={true}
                                   sx={{mb: 1.5}} />
                    <TextField
                        required
                        variant="filled"
                        type="email"
                        label={emailValid !== "" ? emailValid : "Email"}
                        color={emailValid !== "" ? "error" : "primary"}
                        value={email}
                        onChange={(event) => {
                            setEmail(event.target.value);
                            setEmailFocus(false);
                            setEmailValid("");
                        }} sx={{mb: 1.5}}
                        inputProps={{minLength: 3}}
                        focused={emailFocus || emailValid !== ""}
                        onFocus={() => setEmailFocus(true)}
                        onBlur={() => setEmailFocus(false)}
                        onInvalid={event => {
                            event.preventDefault();
                            setEmailValid("Email must be entered");
                        }}
                        pattern="^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$"
                        autoComplete="email" />
                    <TextField
                        required
                        variant="filled"
                        label={firstNameValid !== "" ? firstNameValid : "First name"}
                        color={firstNameValid !== "" ? "error" : "primary"}
                        value={firstName}
                        onChange={(event) => {
                            setFirstName(event.target.value);
                            setFirstNameFocus(false);
                            setFirstNameValid("");
                        }} sx={{mb: 1.5}}
                        inputProps={{pattern: "([a-zA-Z]+){3,255}"}}
                        focused={firstNameFocus || firstNameValid !== ""}
                        onFocus={() => setFirstNameFocus(true)}
                        onBlur={() => setFirstNameFocus(false)}
                        onInvalid={event => {
                            event.preventDefault();
                            if (event.target.value.length < 3) {
                                setFirstNameValid("Min 3 chars");
                            } else if (event.target.value.length > 255) {
                                setFirstNameValid("Max 255 chars");
                            } else if (event.target.value.matchAll("[a-zA-Z]+") !== null) {
                                setFirstNameValid("Type only a-z and A-Z")
                            }
                        }}
                        autoComplete="given-name" />
                    <TextField
                        required
                        variant="filled"
                        label={lastNameValid !== "" ? lastNameValid : "Last name"}
                        color={lastNameValid !== "" ? "error" : "primary"}
                        value={lastName}
                        onChange={(event) => {
                            setLastName(event.target.value);
                            setLastNameFocus(false);
                            setLastNameValid("");
                        }} sx={{mb: 1.5}}
                        inputProps={{pattern: "([a-zA-Z]+){3,255}"}}
                        focused={lastNameFocus || lastNameValid !== ""}
                        onFocus={() => setLastNameFocus(true)}
                        onBlur={() => setLastNameFocus(false)}
                        onInvalid={event => {
                            event.preventDefault();
                            if (event.target.value.length < 3) {
                                setLastNameValid("Min 3 chars");
                            } else if (event.target.value.length > 255) {
                                setLastNameValid("Max 255 chars");
                            } else if (event.target.value.matchAll("[a-zA-Z]+") !== null) {
                                setLastNameValid("Type only a-z and A-Z")
                            }
                        }}
                        autoComplete="family-name" />
                </CardContent>
                <CardActions align="center" display="flex" sx={{paddingY: "20px", justifyContent: "space-around"}}>
                    <Button component={Link} to="/login">Login</Button>
                    <Button variant="contained" type="submit">Register</Button>
                </CardActions>
            </Card>
        </>))
}

export default Register;
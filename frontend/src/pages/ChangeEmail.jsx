import {useState} from "react";
import {Typography, TextField, Button, CardContent, CardActions, Card} from "@mui/material";
import EmailWait from "./EmailWait.jsx";
import GoBack from "../components/GoBack.jsx";
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const ChangeEmail = () => {
    const {security} = useOravixSecurity();
    const [loading, setLoading] = useState(false);
    const [email, setEmail] = useState("");
    const [emailValid, setEmailValid] = useState("");
    const [emailFocus, setEmailFocus] = useState(false);


    const change = e => {
        e.preventDefault();
        security.changeEmail(email, `${window.location.origin}/dash/home`);
    }

    return (loading ? <EmailWait /> : (<>
        <Card
            sx={{width: "350px", height: "max-content", alignSelf: "center"}}
            component="form"
            action="#"
            method="POST"
            onSubmit={change}>
            <CardContent align="center" sx={{paddingBottom: "0!important"}}>
                <Typography gutterBottom variant="h3" component="div">Change Email</Typography>
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
            </CardContent>
            <CardActions align="center" display="flex" sx={{paddingY: "20px", justifyContent: "space-around"}}>
                <GoBack />
                <Button variant="contained" type="submit">Change</Button>
            </CardActions>
        </Card>
    </>))

}

export default ChangeEmail;
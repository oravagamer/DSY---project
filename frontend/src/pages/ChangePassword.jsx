import {useState} from "react";
import {Typography, TextField, Button, CardContent, CardActions, Card} from "@mui/material";
import EmailWait from "./EmailWait.jsx";
import GoBack from "../components/GoBack.jsx";
import useOravixSecurity from "../hooks/useOravixSecurity.js";
import PasswordInput from "../components/PasswordInput.jsx";

const ChangePassword = () => {
    const {security} = useOravixSecurity();
    const [loading, setLoading] = useState(false);
    const [password, setPassword] = useState("");


    const change = e => {
        e.preventDefault();
        security.changePassword(password);
    }

    return (loading ? <EmailWait /> : (<>
        <Card
            sx={{width: "350px", height: "max-content", alignSelf: "center"}}
            component="form"
            action="#"
            method="POST"
            onSubmit={change}>
            <CardContent align="center" sx={{paddingBottom: "0!important"}}>
                <Typography gutterBottom variant="h3" component="div">Change Password</Typography>
                <PasswordInput password={password} setPassword={setPassword} autoComplete="new-password"
                               required={true}
                               sx={{mb: 1.5}} />
            </CardContent>
            <CardActions align="center" display="flex" sx={{paddingY: "20px", justifyContent: "space-around"}}>
                <GoBack />
                <Button variant="contained" type="submit">Change</Button>
            </CardActions>
        </Card>
    </>))

}

export default ChangePassword;
import {useState} from "react";
import {Typography, TextField, Button, CardContent, CardActions, Card} from "@mui/material";
import EmailWait from "./EmailWait.jsx";
import GoBack from "../components/GoBack.jsx";
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const Activate = () => {
    const [username, setUsername] = useState("");
    const {security, winId} = useOravixSecurity();
    const [loading, setLoading] = useState(false);


    const activate = e => {
        e.preventDefault();
        security.activate(username, `${window.location.origin}/login`);
    }

    return (loading ? <EmailWait /> : (<>
        <Card
            sx={{width: "350px", height: "max-content", alignSelf: "center"}}
            component="form"
            action="#"
            method="POST"
            onSubmit={activate}>
            <CardContent align="center" sx={{paddingBottom: "0!important"}}>
                <Typography gutterBottom variant="h3" component="div">Activate account</Typography>
                <TextField
                    label={"Username or Email"}
                    required
                    variant="filled"
                    value={username}
                    onChange={(event) => {
                        setUsername(event.target.value);
                    }}
                    onInvalid={event => {
                        event.preventDefault();
                    }}
                    sx={{mb: 1.5, width: '25ch'}}
                    autoComplete="username email" />
            </CardContent>
            <CardActions align="center" display="flex" sx={{paddingY: "20px", justifyContent: "space-around"}}>
                <GoBack />
                <Button variant="contained" type="submit">Activate</Button>
            </CardActions>
        </Card>
    </>))
}

export default Activate;
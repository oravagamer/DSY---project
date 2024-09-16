import {TextField, Button, CardContent, CardActions, Card} from "@mui/material";
import GoBack from "../components/GoBack.jsx";
import {backendUrl} from "../../settings.js";
import {useState} from "react";
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const AddRole = () => {
    const [name, setName] = useState("");
    const [nameFocused, setNameFocused] = useState(false);
    const [description, setDescription] = useState("");
    const [level, setLevel] = useState(255);
    const [error, setError] = useState(false);
    const {security} = useOravixSecurity();
    const saveChanges = event => {
        event.preventDefault();
        security.secureEncryptedFetch(`${backendUrl}/role/single`, {
            method: "POST", body: JSON.stringify({name: name, description: description, level: level}), headers: {
                "Content-Type": "application/json"
            }
        })
            .then(async res => {
                if (res.status === 409) {
                    setError(true);
                }
            })
    }
    return (<Card sx={{width: "350px", height: "max-content", alignSelf: "center"}}
                  component="form"
                  action="#"
                  method="POST"
                  onSubmit={saveChanges}>
        <CardContent sx={{
            display: "flex",
            flexDirection: "column",
            '& .MuiTextField-root, & .MuiButton-root, & .MuiFormControl-root': {
                m: 1
            }
        }}>
            <TextField value={name} onChange={event => {
                setName(event.target.value);
                setError(false);
            }} color={error ? "error" : "primary"} focused={error || nameFocused} onBlur={() => setNameFocused(false)} onFocus={() => setNameFocused(true)} label={error ? "Role already exists!" : "Name"} variant="filled" />
            <TextField value={description} onChange={event => setDescription(event.target.value)} label="Description"
                       variant="filled" />
            <TextField value={level} label="Level" onChange={event => setLevel(event.target.value)}
                       inputProps={{type: 'number', max: 255, min: 0}} variant="filled" />
        </CardContent>
        <CardActions sx={{justifyContent: "space-between"}}>
            <GoBack />
            <Button variant="contained" type="submit" color="success">Save
                changes</Button>
        </CardActions>
    </Card>)
}

export default AddRole;

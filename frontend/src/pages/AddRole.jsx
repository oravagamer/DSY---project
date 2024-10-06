import {TextField, Button, CardContent, CardActions, Card} from "@mui/material";
import GoBack from "../components/GoBack.jsx";
import {useNavigate} from "react-router-dom";
import {backendUrl} from "../../settings.js";
import {useState} from "react";
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const AddRole = () => {
    const [name, setName] = useState("");
    const [description, setDescription] = useState("");
    const [level, setLevel] = useState(255);
    const {security} = useOravixSecurity();
    const navigate = useNavigate();

    const saveChanges = event => {
        event.preventDefault();
        security.secureEncryptedFetch(`${backendUrl}/role/single`, {
            method: "POST", headers: {
                "Content-Type": "application/json"
            }, body: JSON.stringify({
                name: name,
                description: description,
                level: level
            })
        }).then(res => console.log(res))
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
                <TextField value={name} onChange={event => setName(event.target.value)} label="Name" variant="filled" />
                <TextField value={description} onChange={event => setDescription(event.target.value)}
                           label="Description"
                           variant="filled" />
                <TextField value={level} label="Level" onChange={event => setLevel(event.target.value)}
                           inputProps={{type: 'number', max: 255, min: 0}} variant="filled" />
            </CardContent>
            <CardActions sx={{justifyContent: "space-between"}}>
                <GoBack />
                <Button type="submit" color="success" variant="contained">Create</Button>
            </CardActions>
        </Card>)

}

export default AddRole;
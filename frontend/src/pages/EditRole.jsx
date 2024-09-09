import {Typography, TextField, Button, CardContent, CardActions, Card, ButtonGroup} from "@mui/material";
import GoBack from "../components/GoBack.jsx";
import {useNavigate, useParams} from "react-router-dom";
import useOravixFetch from "../hooks/useOravixFetch.js";
import {backendUrl} from "../../settings.js";
import {useEffect, useState} from "react";
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const EditRole = () => {
    const {id} = useParams();
    const {data, loading} = useOravixFetch(`${backendUrl}/role/single/?role=${id}`, {method: "GET"}, true, true, {
        name: "Loading", description: "Loading", level: 255
    });
    const [name, setName] = useState("");
    const [description, setDescription] = useState("");
    const [level, setLevel] = useState(255);
    const {security} = useOravixSecurity();
    const navigate = useNavigate();

    const saveChanges = event => {
        event.preventDefault();
        security.secureEncryptedFetch(`${backendUrl}/role/single?role=${id}`, {
            method: "PUT", headers: {
                "Content-Type": "application/json"
            }, body: JSON.stringify({
                ...(name === data.name ? {} : {name: name}), ...(description === data.description ? {} : {description: description}), ...(level === data.level ? {} : {level: Number.parseInt(level)})
            })
        })
    }

    const deleteRole = () => {
        security.secureEncryptedFetch(`${backendUrl}/order?id=${id}`, {
            method: "DELETE"
        })
            .then(async res => {
                if (res.status < 400) {
                    navigate("/dash/roles");
                }
            });
    }

    useEffect(() => {
        setName(data.name);
        setDescription(data.description);
        setLevel(data.level);
    }, [loading]);
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
            <TextField value={description} onChange={event => setDescription(event.target.value)} label="Description"
                       variant="filled" />
            <TextField value={level} label="Level" onChange={event => setLevel(event.target.value)}
                       inputProps={{type: 'number', max: 255, min: 0}} variant="filled" />
        </CardContent>
        <CardActions sx={{justifyContent: "space-between"}}>
            <GoBack />
            <ButtonGroup variant="contained">
                <Button type="submit" color="success">Save changes</Button>
                <Button color="error" onClick={deleteRole}>Delete</Button>
            </ButtonGroup>
        </CardActions>
    </Card>)
}

export default EditRole;
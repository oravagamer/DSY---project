import {useNavigate, useParams} from "react-router-dom";
import {backendUrl} from "../../settings.js";
import {useEffect, useState} from "react";
import GoBack from "../components/GoBack.jsx";
import UsersSelect from "../components/UsersSelect.jsx";
import customFetch from "../functions/customFetch.js";
import useOravixSecurity from "../hooks/useOravixSecurity.js";
import {
    Card, CardActions, CardContent, Button, TextField, Select, InputLabel, FormControl, MenuItem, ButtonGroup
} from '@mui/material';
import dayjs from 'dayjs';
import {DatePicker} from '@mui/x-date-pickers/DatePicker';
import useOravixFetch from "../hooks/useOravixFetch.js";

const EditOrder = () => {
    const {id} = useParams();
    const navigate = useNavigate();
    const [finishDate, setFinishDate] = useState();
    const [name, setName] = useState();
    const [description, setDescription] = useState();
    const [status, setStatus] = useState();
    const [user, setUser] = useState();
    const {data, loading} = useOravixFetch(backendUrl + "/order/?id=" + id, {
        method: "GET"
    }, true, true, {});
    const {security} = useOravixSecurity();

    const addImage = event => {
        event.stopPropagation();
        event.preventDefault();
        const file = event.target.files[0];
        customFetch(`${backendUrl}/image?id=${id}&type=${file.name.substring(file.name.lastIndexOf(".") + 1, file.name.size)}`, {
            method: "POST", body: file, headers: {
                "Authorization": `Bearer `
            }
        })
            .then(async res => {
                event.target.value = null;
            });
    }

    const removeImage = event => {
        customFetch(`${backendUrl}/image?id=${event.target.id}`, {
            method: "DELETE", headers: {
                "Authorization": `Bearer `
            }
        })
            .then(async res => {
            });
    }

    const saveChanges = event => {
        event.preventDefault();
        security.secureEncryptedFetch(`${backendUrl}/order?id=${id}`, {
            method: "PUT", headers: {
                "Content-Type": "application/json"
            }, body: JSON.stringify({
                name: name,
                description: description,
                finish_date: finishDate,
                created_for: user === undefined ? null : user,
                status: status === "0" ? null : parseInt(status)
            })
        })
    }

    const deleteOrder = () => {
        security.secureEncryptedFetch(`${backendUrl}/order?id=${id}`, {
            method: "DELETE"
        })
            .then(async res => {
                if (await res.response.status < 400) {
                    await navigate("/dash/home");
                }
            });
    }

    useEffect(() => {
        setName(data?.order?.name);
        setDescription(data?.order?.description);
        setStatus(data?.order?.status === null ? 0 : data?.order?.status);
        setUser(data?.order?.created_for);
        setFinishDate(new Date(data?.order?.finish_date).getTime() / 1000);
    }, [loading]);

    return (<Card
        component="form"
        sx={{width: "350px", alignSelf: "center"}}
        action="#"
        method="POST"
        onSubmit={saveChanges}
    >
        <CardContent>
            <TextField
                required
                variant="filled"
                label="Name"
                value={name === undefined ? "Loading" : name}
                onChange={event => setName(event.target.value)}
                type="text" />
            <TextField
                variant="filled"
                label="Description"
                value={description === undefined ? "Loading" : description}
                onChange={event => setDescription(event.target.value)}
                type="text" />
            <UsersSelect user={user} setUser={setUser} />
            <DatePicker
                label="Finish date"
                format="DD.MM.YYYY"
                value={finishDate === undefined || isNaN(finishDate) ? dayjs(Date.now()) : dayjs.unix(finishDate)}
                onChange={event => setFinishDate(event.unix())}
            />
            <FormControl
                variant="filled"
                sx={{m: 1, minWidth: 120}}>
                <InputLabel
                    id="id-status-select">Status</InputLabel>
                <Select
                    labelId="id-status-select"
                    label="Status"
                    autoWidth
                    value={status === undefined ? 0 : status}
                    onChange={event => setStatus(event.target.value)}
                >
                    <MenuItem value={0}>Created</MenuItem>
                    <MenuItem value={1}>In progress</MenuItem>
                    <MenuItem value={2}>Finished</MenuItem>
                </Select>
            </FormControl>
        </CardContent>
        <CardActions sx={{justifyContent: "space-between"}}>
            <GoBack />
            <ButtonGroup variant="contained">
                <Button type="submit" color="success">Save changes</Button>
                <Button onClick={deleteOrder} color="error">Delete</Button>
            </ButtonGroup>
        </CardActions>
    </Card>)
}

export default EditOrder;
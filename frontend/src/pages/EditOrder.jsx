import {useNavigate, useParams} from "react-router-dom";
import {backendUrl} from "../../settings.js";
import {useEffect, useState} from "react";
import GoBack from "../components/GoBack.jsx";
import UsersSelect from "../components/UsersSelect.jsx";
import useOravixSecurity from "../hooks/useOravixSecurity.js";
import {
    Card,
    CardActions,
    CardContent,
    Button,
    TextField,
    Select,
    InputLabel,
    FormControl,
    MenuItem,
    ButtonGroup,
    Dialog,
    ImageList,
    DialogTitle,
    ImageListItem,
    Box
} from '@mui/material';
import {styled} from '@mui/material/styles';
import UploadFileIcon from '@mui/icons-material/UploadFile';
import dayjs from 'dayjs';
import {DatePicker} from '@mui/x-date-pickers/DatePicker';
import useOravixFetch from "../hooks/useOravixFetch.js";

const VisuallyHiddenInput = styled('input')({
    clip: 'rect(0 0 0 0)',
    clipPath: 'inset(50%)',
    height: 1,
    overflow: 'hidden',
    position: 'absolute',
    bottom: 0,
    left: 0,
    whiteSpace: 'nowrap',
    width: 1,
});

const EditOrder = () => {
    const {id} = useParams();
    const navigate = useNavigate();
    const [finishDate, setFinishDate] = useState();
    const [name, setName] = useState();
    const [description, setDescription] = useState();
    const [status, setStatus] = useState();
    const [user, setUser] = useState();
    const [dialogOpen, setDialogOpen] = useState(false);
    const [hasImages, setHasImages] = useState(false);
    const {data, loading} = useOravixFetch(backendUrl + "/order/?id=" + id, {
        method: "GET"
    }, true, true, {});
    const {security} = useOravixSecurity();

    const addImage = event => {
        event.stopPropagation();
        event.preventDefault();
        for (const file of event.target.files) {
            security.noCryptSecureFetch(`${backendUrl}/image?id=${id}&type=${file.name.substring(file.name.lastIndexOf(".") + 1, file.name.size)}`, {
                method: "POST", body: file
            })
                .then(async res => {

                });
        }
        event.target.value = null;
        setHasImages(true);
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
                if (res.status < 400) {
                    navigate("/dash/home");
                }
            });
    }

    useEffect(() => {
        setName(data?.name);
        setDescription(data?.description);
        setStatus(data?.status === null ? 0 : data?.status);
        setUser(data?.created_for);
        setFinishDate(new Date(data?.finish_date).getTime() / 1000);
        setHasImages(data.images);
    }, [loading]);

    return (<>
        <Card
            component="form"
            sx={{width: "350px", alignSelf: "center"}}
            action="#"
            method="POST"
            onSubmit={saveChanges}
        >
            <CardContent sx={{
                display: "flex",
                flexDirection: "column",
                '& .MuiTextField-root, & .MuiButton-root, & .MuiFormControl-root': {
                    m: 1
                }
            }}>
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
                    sx={{
                        minWidth: 120
                    }}>
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
                <>{loading ? "" : (hasImages ?
                    <Button sx={{alignSelf: "flex-start"}} onClick={() => setDialogOpen(true)}>Images</Button> : "")}</>
                <Button
                    variant="contained"
                    tabIndex={-1}
                    component="label"
                    startIcon={<UploadFileIcon />}>Add images <VisuallyHiddenInput onChange={addImage} type="file"
                                                                                   accept="image/*"
                                                                                   multiple /></Button>
            </CardContent>
            <CardActions sx={{justifyContent: "space-between"}}>
                <GoBack />
                <ButtonGroup variant="contained">
                    <Button type="submit" color="success"
                            disabled={data === {} || (data?.name === name && data?.description === description && new Date(data?.finish_date).getTime() / 1000 === finishDate && data?.status === status && data?.created_for === user)}>Save
                        changes</Button>
                    <Button onClick={deleteOrder} color="error">Delete</Button>
                </ButtonGroup>
            </CardActions>
        </Card>
        <ImageDialog open={dialogOpen} onClose={() => setDialogOpen(false)}
                     title={data?.name} order={id} onHollowChange={hollow => setHasImages(!hollow)} />
    </>)
}

const ImageDialog = ({open, onClose, title, order, onHollowChange}) => {
    const {data, refetch, loading} = useOravixFetch(backendUrl + "/image/all?id=" + order, {
        method: "GET"
    }, true, true, []);
    const {security} = useOravixSecurity();

    const removeImage = id => {
        security.secureEncryptedFetch(`${backendUrl}/image?id=${id}`, {
            method: "DELETE"
        })
            .then(async res => {
                refetch();
            });
    }

    useEffect(() => {
        refetch();
    }, [open]);

    useEffect(() => {
        onHollowChange(data.length === 0);
        if (data.length === 0 && !loading) {
            onClose();
        }
    }, [loading])

    return (<Dialog open={open} onClose={onClose} fullWidth={true} scroll="body" maxWidth="lg">
        <DialogTitle sx={{textAlign: "center"}}>{title} images</DialogTitle>
        {Array.isArray(data) ? <ImageList variant="masonry" cols={3} gap={8}>
            {data?.map(value => {
                return <ImageItem onClick={() => removeImage(value)} key={value} imageId={value} />
            })}
        </ImageList> : ""}
    </Dialog>)
}

const ImageItem = ({
                       imageId, onClick = () => {
    }
                   }) => {
    const [hover, setHover] = useState(false);
    return <ImageListItem onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}>
        {hover ? <Box sx={{
            zIndex: 100,
            position: "absolute",
            display: "flex",
            justifyContent: "center",
            width: "100%",
            height: "100%",
            alignItems: "center"
        }}><Button color="error"
                   variant="contained"
                   onClick={onClick}>Delete</Button></Box> : ""}
        <img src={`${backendUrl}/image?id=${imageId}`} alt={imageId} />
    </ImageListItem>
};

export default EditOrder;
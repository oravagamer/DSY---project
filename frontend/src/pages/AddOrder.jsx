import {useNavigate, useParams} from "react-router-dom";
import {backendUrl} from "../../settings.js";
import {useState, useEffect} from "react";
import GoBack from "../components/GoBack.jsx";
import UsersSelect from "../components/UsersSelect.jsx";
import useOravixSecurity from "../hooks/useOravixSecurity.js";
import {
    Card, CardActions, CardContent, Button, TextField, Dialog, ImageList, DialogTitle, ImageListItem, Box
} from '@mui/material';
import {styled} from '@mui/material/styles';
import dayjs from 'dayjs';
import {DatePicker} from '@mui/x-date-pickers/DatePicker';

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

const AddOrder = () => {
    const {id} = useParams();
    const navigate = useNavigate();
    const [finishDate, setFinishDate] = useState(dayjs(Date.now()));
    const [name, setName] = useState("");
    const [description, setDescription] = useState("");
    const [user, setUser] = useState();
    const [dialogOpen, setDialogOpen] = useState(false);
    const [error, setError] = useState(false);
    const [nameFocus, setNameFocus] = useState(false);
    const [images, setImages] = useState([]);
    const {security} = useOravixSecurity();

    const addImage = event => {
        event.stopPropagation();
        event.preventDefault();
        setImages([...images, ...event.target.files])
        event.target.value = null;
    }

    const saveChanges = event => {
        event.preventDefault();
        let data = new FormData();
        data.append("name", name);
        data.append("description", description);
        data.append("finish_date", finishDate.unix());
        if (user !== undefined) {
            data.append("created_for", user);
        }
        if (images !== []) {
            for (const image of images) {
                data.append("images[]", image);
            }
        }
        security.noCryptSecureFetch(`${backendUrl}/order`, {
            method: "POST", body: data
        })
            .then(async res => {
                if (res.status == 409) {
                    setError(true);
                }
            })
    }

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
                    label={error ? "Name already used" : "Name"}
                    value={name}
                    color={error ? "error" : "primary"}
                    onChange={event => {
                        setError(false)
                        setName(event.target.value)
                    }}
                    focused={error || nameFocus}
                    onFocus={() => setNameFocus(true)}
                    onBlur={() => setNameFocus(false)}
                    type="text" />
                <TextField
                    variant="filled"
                    label="Description"
                    value={description}
                    onChange={event => setDescription(event.target.value)}
                    type="text" />
                <UsersSelect user={user} setUser={setUser} />
                <DatePicker
                    label="Finish date"
                    format="DD.MM.YYYY"
                    value={finishDate}
                    onChange={event => setFinishDate(event)}
                />
                <Button sx={{alignSelf: "stretch"}} onClick={() => setDialogOpen(true)}>Images</Button>
            </CardContent>
            <CardActions sx={{justifyContent: "space-between"}}>
                <GoBack />
                <Button variant="contained" type="submit" color="success">Save
                    changes</Button>
            </CardActions>
        </Card>
        <Dialog open={dialogOpen} onClose={() => setDialogOpen(false)} fullWidth={true} scroll="body" maxWidth="lg">
            <DialogTitle>Add images to new order</DialogTitle>
            <ImageList variant="masonry" cols={3} gap={8}>
                <ImageListItem tabIndex={-1}
                               component="label" sx={{
                    border: "5px solid #475c80", borderRadius: "5px", "&:hover": {
                        backgroundColor: "#7b7b7b63"
                    }
                }}>
                    <img src="/upload_foto.svg" style={{filter: "invert(1)"}} />
                    <VisuallyHiddenInput onChange={addImage} type="file"
                                         accept="image/*"
                                         multiple />
                </ImageListItem>
                {images.map && images.map((value, index) => <ImageItem key={index} src={URL.createObjectURL(value)}
                                                                       onClick={() => setImages([...images.slice(0, index), ...images.slice(index + 1, images.length)])} />)}
            </ImageList>
        </Dialog>
    </>)

}
const ImageItem = ({
                       src, onClick = () => {
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
        <img src={src} alt={src} />
    </ImageListItem>
};
export default AddOrder;
import {backendUrl} from "../../settings.js";
import {useState, useEffect} from "react";
import {Link, useParams} from "react-router-dom";
import GoBack from "../components/GoBack.jsx";
import {
    Card, CardActions, CardContent, Button, Typography, Dialog, DialogTitle, ImageList, ImageListItem
} from '@mui/material';
import useOravixFetch from "../hooks/useOravixFetch.js";
import useOravixSecurity from "../hooks/useOravixSecurity.js";

const Order = () => {
    const {id} = useParams();
    const [dialogOpen, setDialogOpen] = useState(false);
    const {data} = useOravixFetch(backendUrl + "/order/?id=" + id, {
        method: "GET"
    }, true, true, {
        name: "Loading", description: "Loading", created_date: "Loading", status: -1
    });
    const {getUserId, security} = useOravixSecurity();
    const {data: imgData, loading: imgLoading} = useOravixFetch(backendUrl + "/image/all?id=" + id, {
        method: "GET"
    }, true, true, []);
    const {data: roles} = useOravixFetch(`${backendUrl}/role`, {method: "GET"}, true, true, []);

    return (<>
        <Card sx={{minWidth: "300px", alignSelf: "center", borderRadius: "5px"}} variant="outlined">
            <CardContent>
                <Typography variant="h4" component="div">{data.name}</Typography>
                <Typography sx={{fontSize: 14}} color="text.secondary"
                            gutterBottom>{data.description}</Typography>
                <Typography variant="body2">Created: {data.created_date}</Typography>
                <Typography variant="body2">Finish: {data.finish_date}</Typography>
                <Typography
                    variant="body2">Status: {data.status === -1 ? "Loading" : (data.status === null ? "Created" : data.status === 1 ? "In progress" : "Finished")}</Typography>
                <>{imgLoading ? "" : (data?.images ?
                    <Button sx={{alignSelf: "flex-start"}} onClick={() => setDialogOpen(true)}>Images</Button> : "")}</>
            </CardContent>
            <CardActions sx={{justifyContent: "space-between"}}>
                <GoBack />
                <Button component={Link} to="edit" variant="contained"
                        disabled={data.created_for !== getUserId() && data.created_by !== getUserId() && roles.find(value => value.name === "admin") === undefined}>Edit</Button>
            </CardActions>
        </Card>
        <Dialog open={dialogOpen} onClose={() => setDialogOpen(false)} fullWidth={true} scroll="body" maxWidth="lg">
            <DialogTitle sx={{textAlign: "center"}}>{data?.name} images</DialogTitle>
            {Array.isArray(imgData) ? <ImageList variant="masonry" cols={3} gap={8}>
                {imgData?.map(value => {
                    return (<ImageListItem key={value}>
                        <img src={`${backendUrl}/image?id=${value}`} alt={value} />
                    </ImageListItem>)
                })}
            </ImageList> : ""}
        </Dialog>
    </>)
}

export default Order;
import {backendUrl} from "../../settings.js";
import {useState, useEffect} from "react";
import {Link, useParams} from "react-router-dom";
import GoBack from "../components/GoBack.jsx";
import {
    Card, CardActions, CardContent, Button, Typography, Dialog, DialogTitle, ImageList, ImageListItem
} from '@mui/material';
import useOravixFetch from "../hooks/useOravixFetch.js";

const Order = () => {
    const {id} = useParams();
    const [dialogOpen, setDialogOpen] = useState(false);
    const {data, loading} = useOravixFetch(backendUrl + "/order/?id=" + id, {
        method: "GET"
    }, true, true);

    return (<>
        <Card sx={{width: "300px", alignSelf: "center", borderRadius: "5px"}} variant="outlined">
            <CardContent>
                <Typography variant="h4" component="div">{loading ? "Loading" : data.order.name}</Typography>
                <Typography sx={{fontSize: 14}} color="text.secondary"
                            gutterBottom>{loading ? "Loading" : data.order.description}</Typography>
                <Typography variant="body2">Created: {loading ? "Loading" : data.order.created_date}</Typography>
                <Typography variant="body2">Finish: {loading ? "Loading" : data.order.finish_date}</Typography>
                <Typography
                    variant="body2">Status: {loading ? "Loading" : (data.order.status === null ? "Created" : data.order.status === 1 ? "In progress" : "Finished")}</Typography>
                <>{loading ? "" : (!(data.images.length === 0) ?
                    <Button sx={{alignSelf: "flex-start"}} onClick={() => setDialogOpen(true)}>Images</Button> : "")}</>
            </CardContent>
            <CardActions sx={{justifyContent: "space-between"}}>
                <GoBack />
                <Button component={Link} to="edit" variant="contained">Edit</Button>
            </CardActions>
        </Card>
        <Dialog open={dialogOpen} onClose={() => setDialogOpen(false)} fullWidth={true} scroll="body" maxWidth="lg">
            <DialogTitle sx={{textAlign: "center"}}>{data?.order?.name} images</DialogTitle>
            {Array.isArray(data?.images) ? <ImageList variant="masonry" cols={3} gap={8}>
                {data?.images?.map(value => {
                    return (<ImageListItem key={value}>
                        <img src={`${backendUrl}/image?id=${value}`} alt={value} />
                    </ImageListItem>)
                })}
            </ImageList> : ""}
        </Dialog>
    </>)
}

export default Order;
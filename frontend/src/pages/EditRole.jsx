import {Typography, TextField, Button, CardContent, CardActions, Card, ButtonGroup} from "@mui/material";
import GoBack from "../components/GoBack.jsx";

const EditRole = () => {
    return (<Card sx={{width: "350px", height: "max-content", alignSelf: "center"}}
                  component="form"
                  action="#"
                  method="POST">
        <CardContent>

        </CardContent>
        <CardActions sx={{justifyContent: "space-between"}}>
            <GoBack />
            <ButtonGroup variant="contained">
                <Button type="submit" color="success">Save changes</Button>
                <Button color="error">Delete</Button>
            </ButtonGroup>
        </CardActions>
    </Card>)
}

export default EditRole;
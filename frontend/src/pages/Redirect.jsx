import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from "@mui/material/Typography";
import LinearProgress from '@mui/material/LinearProgress';
import CardActions from '@mui/material/CardActions';
import {useState, useEffect} from "react";

const Redirect = () => {
    const [count, setCount] = useState(5);

    useEffect(() => {
        setTimeout(() => {
            if (count === 0) {
                window.close();
            } else {
                setCount(count - 1);
            }
        }, 1000);
    }, [count]);



    return (
        <Card sx={{width: "350px", height: "max-content", alignSelf: "center", borderRadius: "25px"}}>
            <CardContent align="center" sx={{paddingBottom: "0!important"}}>
                <Typography gutterBottom variant="h3" component="div">Redirect</Typography>
                <Typography gutterBottom>This window will close in {count} {count === 1 ? "second" : "seconds"}. Please use that window from action was
                    performed.</Typography>
            </CardContent>
            <CardActions>
                <LinearProgress variant="determinate" value={(count * 100) / 5} sx={{display: "inline-block", width: "100%", margin: "0 10px"}} />
            </CardActions>
        </Card>
    )
}

export default Redirect;
import {Button} from '@mui/material';

const GoBack = (props) => {
    return (<Button className={props.className} id={props.id} onClick={() => window.history.back()}>Go back</Button>)
}

export default GoBack;
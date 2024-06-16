const GoBack = (props) => {
    return (<button className={props.className} id={props.id} onClick={() => window.history.back()}>Go back</button>)
}

export default GoBack;
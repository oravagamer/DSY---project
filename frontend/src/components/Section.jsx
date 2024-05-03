const Section = (props) => {
    return (<section className={props.className} id={props.id}>{props.children}</section>)
}

export default Section;
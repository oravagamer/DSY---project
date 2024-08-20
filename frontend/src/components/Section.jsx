const Section = ({className, children, id}) => {
    return (<section className={"section " + className} id={id}>{children}</section>)
}

export default Section;
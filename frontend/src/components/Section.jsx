import styles from "./Section.module.scss";

const Section = (props) => {
    return (<section className={styles["section"] + " " + props.className} id={props.id}>{props.children}</section>)
}

export default Section;
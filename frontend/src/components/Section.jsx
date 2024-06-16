import styles from "./Section.module.scss";

const Section = ({className, children, id}) => {
    return (<section className={styles["section"] + " " + className} id={id}>{children}</section>)
}

export default Section;
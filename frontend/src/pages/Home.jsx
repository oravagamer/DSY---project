import {backendUrl, frontendUrl} from "../../settings.js";
import {Link} from "react-router-dom";
import {useState, useEffect} from "react";
import React from "react";
import {
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableSortLabel,
    TableHead,
    TablePagination,
    TableRow,
    FormControlLabel,
    Grid,
    Switch
} from '@mui/material';
import useOravixFetch from "../hooks/useOravixFetch.js";

const Home = () => {
    const [page, setPage] = useState(0);
    const [rowsPerPage, setRowsPerPage] = useState(10);
    const [my, setMy] = useState(false);
    const [completed, setCompleted] = useState(false);
    const [sorting, setSorting] = useState({
        sortBy: "date_created", asc: false
    });
    const {
        data, refetch, status
    } = useOravixFetch(`${backendUrl}/orders/?page=${page}&count=${rowsPerPage}&sort-by=${sorting.sortBy}&asc=${+sorting.asc}&completed=${+completed}&only-my=${+my}`, {
        method: "GET"
    }, true, true, []);

    const handleChangePage = (event, newPage) => {
        setPage(newPage);
        refetch();
    };
    const handleChangeRowsPerPage = (event) => {
        setRowsPerPage(+event.target.value);
        setPage(0);
        refetch();
    };

    return (<>
        <Grid container>
            <Grid item sx={{pl: "5px"}}>
                <FormControlLabel control={<Switch onChange={e => {
                    setMy(e.target.checked)
                    refetch()
                }} />} label="Only my" />
            </Grid>
            <Grid item>
                <FormControlLabel control={<Switch onChange={e => {
                    setCompleted(e.target.checked)
                    refetch()
                }} />} label="Completed" />
            </Grid>
        </Grid>
        <TableContainer
            sx={{width: "100%", flexGrow: 1, height: "100%"}}>
            <Table
                stickyHeader>
                <TableHead>
                    <TableRow>
                        {["name", "date_created", "finish_date", "status",].map(value => <TableCell key={value}>
                            <TableSortLabel
                                active={sorting.sortBy === value}
                                direction={sorting.asc ? "asc" : "desc"}
                                onClick={() => {
                                    setSorting({
                                        sortBy: value, asc: sorting.sortBy === value ? !sorting.asc : sorting.asc
                                    });
                                    refetch();
                                }}
                            >
                                {value
                                    .split("_")
                                    .map(value => value.charAt(0).toUpperCase() + value.slice(1))
                                    .join(" ")}
                            </TableSortLabel>
                        </TableCell>)}
                        <TableCell>Action</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {data?.orders?.map && data?.orders?.map(value => {
                        return (<TableRow
                            key={value.id}
                            hover>
                            <TableCell>{value.name}</TableCell>
                            <TableCell>{value.created_date}</TableCell>
                            <TableCell>{value.finish_date}</TableCell>
                            <TableCell>{value.status === null || value.status === undefined || value.status === 0 ? "Created" : (value.status === 1 ? "In progress" : "Finished")}</TableCell>
                            <TableCell><Link to={`/dash/order/${value.id}`}>More</Link></TableCell>
                        </TableRow>)
                    })}
                </TableBody>
            </Table>
        </TableContainer>
        <TablePagination
            rowsPerPageOptions={[10, 25, 100]}
            component="div"
            count={data.count === undefined ? 0 : data.count}
            rowsPerPage={rowsPerPage}
            sx={{overflow: "hidden"}}
            page={page}
            onPageChange={handleChangePage}
            onRowsPerPageChange={handleChangeRowsPerPage} />
    </>)
}

export default Home;
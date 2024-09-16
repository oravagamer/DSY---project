import {useState, useEffect} from "react";
import {
    IconButton,
    FormControl,
    Input,
    InputAdornment,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableSortLabel,
    TableHead,
    TablePagination,
    TableRow,
    Divider,

} from '@mui/material';
import {Link} from "react-router-dom";
import useOravixFetch from "../hooks/useOravixFetch.js";
import {backendUrl} from "../../settings.js";

const Roles = () => {
    const [sorting, setSorting] = useState({
        sortBy: "name", asc: false
    });
    const [page, setPage] = useState(0);
    const [rowsPerPage, setRowsPerPage] = useState(10);
    const {
        data, refetch, status
    } = useOravixFetch(`${backendUrl}/role/all/?page=${page}&count=${rowsPerPage}&sort-by=${sorting.sortBy}&asc=${+sorting.asc}`, {
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
    return (<><TableContainer sx={{width: "100%", flexGrow: 1, height: "100%"}}>
        <Table stickyHeader>
            <TableHead>
                <TableRow>
                    {["name", "level"].map(value => <TableCell key={value}>
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
                    <TableCell>Description</TableCell>
                    <TableCell>Action</TableCell>
                </TableRow>
            </TableHead>
            <TableBody>
                {
                    data?.roles?.map && data?.roles.map(value => <TableRow key={value.id}>
                        <TableCell>{value.name}</TableCell>
                        <TableCell>{value.level}</TableCell>
                        <TableCell>{value.description}</TableCell>
                        {value.name === "admin" || value.name === "default" ? <TableCell /> :
                            <TableCell><Link to={`edit/${value.id}`}>Edit</Link></TableCell>}
                    </TableRow>)
                }
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
            onRowsPerPageChange={handleChangeRowsPerPage} /></>)
}

export default Roles;
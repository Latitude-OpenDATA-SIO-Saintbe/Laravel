import * as React from "react";
import {
    ColumnDef,
    ColumnFiltersState,
    SortingState,
    VisibilityState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useReactTable,
} from "@tanstack/react-table";
import axios from 'axios';
import { Input } from "@/components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { DashboardLayout } from "@/Layouts/Dashboard";
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MoreHorizontal } from "lucide-react";

interface DataRow {
    id: string; // Required field
    [key: string]: string | number | undefined; // Optional fields
}

const DataTable: React.FC = () => {
    const [tables, setTables] = React.useState<string[]>([]);
    const [data, setData] = React.useState<DataRow[]>([]);
    const [sorting, setSorting] = React.useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
    const [rowSelection, setRowSelection] = React.useState({});
    const [editRow, setEditRow] = React.useState<DataRow | null>(null);
    const [newRow, setNewRow] = React.useState<DataRow>({ id: '', /* initialize other fields */ });
    const [currentTable, setCurrentTable] = React.useState<string>('');

    React.useEffect(() => {
        axios.get('/api/data')
            .then(response => {
                setTables(response.data);
            })
            .catch(error => {
                console.error("Error fetching tables:", error);
            });
    }, []);

    const fetchData = (table: string) => {
        if (table) {
            axios.get(`/api/data/${table}`)
                .then(response => {
                    setData(response.data);
                    setCurrentTable(table);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                });
        } else {
            setData([]);
        }
    };

    const columns: ColumnDef<DataRow>[] = React.useMemo(() => {
        if (data.length > 0) {
            return Object.keys(data[0]).map(key => ({
                accessorKey: key,
                header: key.charAt(0).toUpperCase() + key.slice(1),
                cell: ({ row }) => <div>{row.getValue(key)}</div>,
            }));
        }
        return [];
    }, [data]);

    const table = useReactTable({
        data,
        columns,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        onColumnVisibilityChange: setColumnVisibility,
        onRowSelectionChange: setRowSelection,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            rowSelection,
        },
    });

    const handleAddRow = () => {
        const newRowData: DataRow = {
            ...newRow,
            id: String(newRow.id), // Convert id to string
        };

        axios.post('/api/data', newRowData)
            .then(response => {
                setData(prev => [...prev, response.data]);
                setNewRow({ id: '' }); // Reset newRow
            })
            .catch(error => {
                console.error("Error adding row:", error);
            });
    };

    const handleUpdateRow = () => {
        if (editRow) {
            const updatedRowData: DataRow = {
                ...editRow,
                id: String(editRow.id), // Convert id to string
            };

            axios.put(`/api/data/${updatedRowData.id}`, updatedRowData)
                .then(response => {
                    setData(prev => prev.map(item => (item.id === response.data.id ? response.data : item)));
                    setEditRow(null);
                })
                .catch(error => {
                    console.error("Error updating row:", error);
                });
        }
    };

    const handleEditRow = (row: DataRow) => {
        setEditRow(row);
        setNewRow(row); // Populate form with the selected row data
    };

    const handleDeleteRow = (id: string, table: string) => {
        axios.delete(`/api/data/${table}/${id}`)
            .then(() => {
                setData(prev => prev.filter(item => item.id !== id));
            })
            .catch(error => {
                console.error("Error deleting row:", error);
            });
    };

    return (
        <DashboardLayout>
            <div>
                <h1 className="text-3xl font-bold text-gray-800 mb-6">Data</h1>
                <div>
                    <label htmlFor="tables" className="block text-sm font-medium text-gray-700 mb-2">
                        Select Table
                    </label>
                    <select
                        id="tables"
                        onChange={(e) => fetchData(e.target.value)}
                        className="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mb-4"
                    >
                        <option value="">-- Select a Table --</option>
                        {tables.map((table) => (
                            <option key={table} value={table}>
                                {table}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Form for Adding/Editing Rows */}
                <div className="mb-4">
                    <h2 className="text-xl font-semibold">Add/Edit Row</h2>
                    <Input
                        placeholder="Field 1"
                        value={newRow.field1 || ''}
                        onChange={(e) => setNewRow({ ...newRow, field1: e.target.value })}
                        className="mb-2"
                    />
                    <Button onClick={editRow ? handleUpdateRow : handleAddRow}>
                        {editRow ? "Update Row" : "Add Row"}
                    </Button>
                </div>

                <div className="flex items-center mb-4">
                    <Input
                        placeholder="Filter by email..."
                        value={(table.getColumn("email")?.getFilterValue() as string) ?? ""}
                        onChange={(event) =>
                            table.getColumn("email")?.setFilterValue(event.target.value)
                        }
                        className="max-w-sm"
                    />
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            {table.getHeaderGroups().map(headerGroup => (
                                <TableRow key={headerGroup.id}>
                                    {headerGroup.headers.map(header => (
                                        <TableHead key={header.id}>
                                            {flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    ))}
                                </TableRow>
                            ))}
                        </TableHeader>
                        <TableBody>
                            {table.getRowModel().rows.length ? (
                                table.getRowModel().rows.map(row => (
                                    <TableRow key={row.id}>
                                        {row.getVisibleCells().map(cell => (
                                            <TableCell key={cell.id}>
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </TableCell>
                                        ))}
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost">
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>Edit/Delete</DropdownMenuLabel>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem onClick={() => handleEditRow(row.original)}>Edit</DropdownMenuItem>
                                                    <DropdownMenuItem onClick={() => handleDeleteRow(row.original.id, currentTable)}>Delete</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={columns.length}>
                                        No data available.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </DashboardLayout>
    );
};

export default DataTable;

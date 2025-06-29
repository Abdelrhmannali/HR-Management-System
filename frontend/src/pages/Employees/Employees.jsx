import React, { useState, useEffect, useRef } from "react";
import { useQuery } from "@tanstack/react-query";
import { useNavigate, Link, useSearchParams, Outlet } from "react-router-dom";
import ShowEmployeeModal from "../../components/ShowEmployeeModal";
import api from "../../api";
import { useQueryClient } from "@tanstack/react-query";

export default function Employees() {
  const navigate = useNavigate();
  const [showModel, setShowModel] = useState(false);
  const [selectedEmployee, setSelectedEmployee] = useState(null);
  const [employees, setEmployees] = useState([]);
  const [totalPages, setTotalPages] = useState(1);
  const [searchParams, setSearchParams] = useSearchParams();
  const page = parseInt(searchParams.get("page")) || 1;
  const searchInputRef = useRef(null);
  const queryClient = useQueryClient();

  const {
    data: employeeData,
    isLoading,
    isError,
    isFetching,
  } = useQuery({
    queryKey: ["employees", page],
    queryFn: () => api.get(`/employees?page=${page}`).then((res) => res.data),
    keepPreviousData: true,
  });

  console.log(employeeData?.data);

  const {
    data: departmentsData,
    isLoading: deptLoading,
    isError: deptError,
  } = useQuery({
    queryKey: ["departments"],
    queryFn: () => api.get("/departments").then((res) => res.data),
  });

  useEffect(() => {
    if (employeeData?.last_page) setTotalPages(employeeData.last_page);
  }, [employeeData]);

  const handleDelete = async (id) => {
    if (confirm("Are you sure you want to delete this employee?")) {
      try {
        await api.delete(`/employees/${id}`);

        queryClient.invalidateQueries(["employees"]);

        setEmployees([]);
      } catch (error) {
        alert("Failed to delete employee");
      }
    }
  };

  //  Edit
  const handleEdit = (id) => navigate(`/employees/edit/${id}`);

  //  Show
  const handleShow = (employee) => {
    setSelectedEmployee(employee);
    setShowModel(true);
  };

  //  Search
  const handleSearch = (e) => {
    e.preventDefault();
    const query = searchInputRef.current?.value.trim().toLowerCase();
    if (!query) return;

    api
      .get(`/employees/search?query=${query}`)
      .then((res) => {
        const results = Array.isArray(res.data?.data)
          ? res.data.data
          : res.data;
        setEmployees(results);
      })
      .catch(() => alert("Error while searching"));
  };

  //  Reset
  const handleReset = () => {
    setEmployees([]);
    if (searchInputRef.current) searchInputRef.current.value = "";
  };

  // New Employees Today
  const startOfToday = new Date();
  startOfToday.setHours(0, 0, 0, 0);

  const endOfToday = new Date(startOfToday);
  endOfToday.setDate(endOfToday.getDate() + 1);

  const todayNewEmployees = (employeeData?.data || []).filter((emp) => {
    if (!emp.created_at) return false;
    const createdAt = new Date(emp.created_at);
    return createdAt >= startOfToday && createdAt < endOfToday;
  }).length;

  const list = employees.length > 0 ? employees : employeeData?.data;

  if (isLoading || deptLoading || isFetching) return <div>Loading...</div>;
  if (isError || deptError) return <div>Error loading data</div>;

  return (
    <div className="container py-4 px-4">
      {/* Header */}
      <div className="row align-items-start mb-4">
        <div className="col-12 col-md-6 mb-3 mb-md-0">
          <h2 style={{ color: "#ac70c6" }}>
            <i className="fa-solid fa-users me-2" /> Employees
          </h2>
        </div>

        <div className="col-12 col-md-6">
          <form className="d-flex" role="search" onSubmit={handleSearch}>
            <input
              ref={searchInputRef}
              className="form-control me-2 border-2"
              type="search"
              placeholder="Search by name..."
              style={{ borderColor: "#ac70c6" }}
            />
            <button
              className="btn border-2"
              type="submit"
              style={{
                backgroundColor: "#ac70c6",
                color: "white",
                borderColor: "#ac70c6",
              }}
            >
              Search
            </button>
            <button
              className="btn border-2 ms-2"
              type="button"
              onClick={handleReset}
              style={{
                backgroundColor: "#9b59b6",
                color: "white",
                borderColor: "#9b59b6",
              }}
            >
              Reset
            </button>
          </form>
        </div>
      </div>

      {/* Cards */}
      <div className="mb-4 d-flex flex-wrap gap-3 justify-content-between align-items-start">
        {/* Total Employees */}
        <div
          className="card text-dark bg-light"
          style={{
            minWidth: "18rem",
            borderRadius: "20px",
            boxShadow: "0 4px 10px rgba(172, 112, 198, 0.2)",
            flex: "1 1 auto",
            borderTop: "4px solid #ac70c6",
          }}
        >
          <div className="card-body d-flex flex-column align-items-start">
            <div className="d-flex align-items-center mb-2">
              <i
                className="fa-solid fa-circle-user fa-3x me-3"
                style={{ color: "#ac70c6" }}
              ></i>
              <div>
                <p className="mb-1 fw-semibold text-muted">Total Employees</p>
                <h5 className="card-title mb-0" style={{ color: "#ac70c6" }}>
                  {employeeData?.total ?? "..."}
                </h5>
              </div>
            </div>
            <p className="card-text small text-muted text-center w-100 mt-2">
              Number of all employees in the system
            </p>
          </div>
        </div>

        {/* Total Departments */}
        <div
          className="card text-dark bg-light"
          style={{
            minWidth: "18rem",
            borderRadius: "20px",
            boxShadow: "0 4px 10px rgba(172, 112, 198, 0.2)",
            flex: "1 1 auto",
            borderTop: "4px solid #9b59b6",
          }}
        >
          <div className="card-body d-flex flex-column align-items-start">
            <div className="d-flex align-items-center mb-2">
              <i
                className="fa-solid fa-building fa-3x me-3"
                style={{ color: "#9b59b6" }}
              ></i>
              <div>
                <p className="mb-1 fw-semibold text-muted">Total Departments</p>
                <h5 className="card-title mb-0" style={{ color: "#9b59b6" }}>
                  {departmentsData?.length ?? "..."}
                </h5>
              </div>
            </div>
            <p className="card-text small text-muted text-center w-100 mt-2">
              Number of departments in the system
            </p>
          </div>
        </div>

        {/* New Employees Today */}
        <div
          className="card text-dark bg-light"
          style={{
            minWidth: "18rem",
            borderRadius: "20px",
            boxShadow: "0 4px 10px rgba(172, 112, 198, 0.2)",
            flex: "1 1 auto",
            borderTop: "4px solid #8e44ad",
          }}
        >
          <div className="card-body d-flex flex-column align-items-start">
            <div className="d-flex align-items-center mb-2">
              <i
                className="fa-solid fa-user-plus fa-3x me-3"
                style={{ color: "#8e44ad" }}
              ></i>
              <div>
                <p className="mb-1 fw-semibold text-muted">New Today</p>
                <h5 className="card-title mb-0" style={{ color: "#8e44ad" }}>
                  {todayNewEmployees}
                </h5>
              </div>
            </div>
            <p className="card-text small text-muted text-center w-100 mt-2">
              Employees added today
            </p>
          </div>
        </div>
      </div>

      {/* Add Employee Button */}
      <div className="col-12 col-md-3 mb-2">
        <Link to="/employees/add">
          <button
            className="btn text-white px-4 py-2"
            style={{ borderRadius: "10px", background: "#ac70c6" }}
          >
            <i className="fa fa-plus me-2" /> Add Employee
          </button>
        </Link>
      </div>

      {/* Table */}

      <table
        className="table table-light "
        style={{
          borderRadius: "30px",
          boxShadow: "0 4px 10px rgba(172, 112, 198, 0.1)",
        }}
      >
        <thead
          style={{
            backgroundColor: "#f8f9fa",
            borderBottom: "2px solid #ac70c6",
          }}
        >
          <tr>
            <th
              className="ps-4"
              style={{ color: "#ac70c6", fontWeight: "600" }}
            >
              #
            </th>
            <th
              style={{ color: "#ac70c6", fontWeight: "600" }}
              className="ps-4 text-centre"
            >
              Name
            </th>
            <th
              style={{ color: "#ac70c6", fontWeight: "600" }}
              className="ps-0"
            >
              Department
            </th>
            <th style={{ color: "#ac70c6", fontWeight: "600" }}>Email</th>
            <th style={{ color: "#ac70c6", fontWeight: "600" }}>Phone</th>
            <th style={{ color: "#ac70c6", fontWeight: "600" }}>Actions</th>
          </tr>
        </thead>
        <tbody>
          {list?.map((emp, idx) => (
            <tr key={emp.id}>
              <td className="ps-4 pe-4">{idx + 1}</td>
              <td>
                <img
                  src={`http://127.0.0.1:8000/storage/${emp.profile_picture}`}
                  alt="avatar"
                  width="50"
                  height="50"
                  className="rounded-circle me-2"
                  style={{ border: "2px solid #ac70c6" }}
                />
                {emp.first_name} {emp.last_name}
              </td>
              <td className=" text-centre">{emp.department?.dept_name}</td>
              <td>{emp.email}</td>
              <td>{emp.phone}</td>
              <td className="pe-0">
                <button
                  className="btn btn-sm me-2"
                  onClick={() => handleShow(emp)}
                  style={{
                    backgroundColor: "#ac70c6",

                    color: "white",
                    border: "none",
                  }}
                >
                  <i className="fa-solid fa-eye" />
                </button>
                <button
                  className="btn btn-sm me-2 $pink-400 "
                  onClick={() => handleEdit(emp.id)}
                  style={{
                    backgroundColor: "#9b59b6",

                    color: "white",
                    border: "none",
                  }}
                >
                  <i className="fa-solid fa-user-pen" />
                </button>
                <button
                  className="btn btn-sm"
                  onClick={() => handleDelete(emp.id)}
                  style={{
                    backgroundColor: "#8e44ad",
                    color: "white",
                    border: "none",
                  }}
                >
                  <i className="fa-solid fa-trash-can" />
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {/* Pagination */}
      <div className="d-flex justify-content-center flex-wrap mt-4">
        {Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
          <button
            key={p}
            className="btn btn-sm mx-1"
            style={{
              backgroundColor: p === page ? "#ac70c6" : "transparent",
              color: p === page ? "white" : "#ac70c6",
              border: "2px solid #ac70c6",
              borderRadius: "8px",
            }}
            onClick={() => setSearchParams({ page: p })}
          >
            {p}
          </button>
        ))}
      </div>

      {/* Modal */}
      <ShowEmployeeModal
        show={showModel}
        onHide={() => setShowModel(false)}
        employee={selectedEmployee}
      />

      <Outlet />
    </div>
  );
}

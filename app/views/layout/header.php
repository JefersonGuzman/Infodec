<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VentasPlus - Sistema de Gestión de Comisiones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .sidebar .nav-link {
            color: #000;
            padding: 12px 20px;
            border-radius: 0;
            border-bottom: 1px solid #e9ecef;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef;
            color: #000;
        }
        .sidebar .nav-link.active {
            background-color: #000;
            color: #fff;
        }
        .main-content {
            background-color: #fff;
            min-height: 100vh;
        }
        .btn-primary {
            background-color: #000;
            border-color: #000;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
            color: #fff;
        }
        .btn-outline-primary {
            color: #000;
            border-color: #000;
        }
        .btn-outline-primary:hover {
            background-color: #000;
            border-color: #000;
            color: #fff;
        }
        .table {
            background-color: #fff;
        }
        .table th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #000;
        }
        .card {
            border: 1px solid #dee2e6;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .form-control {
            border-color: #dee2e6;
        }
        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h4 class="text-dark mb-0">
                            <i class="bi bi-graph-up-arrow"></i> VentasPlus
                        </h4>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php?controller=Ventas&action=index">
                            <i class="bi bi-upload me-2"></i> Cargar Ventas
                        </a>
                        <a class="nav-link" href="index.php?controller=Devoluciones&action=index">
                            <i class="bi bi-arrow-return-left me-2"></i> Cargar Devoluciones
                        </a>
                        <a class="nav-link" href="index.php?controller=Vendedores&action=index">
                            <i class="bi bi-people me-2"></i> Gestionar Vendedores
                        </a>
                        <a class="nav-link" href="index.php?controller=Comisiones&action=index">
                            <i class="bi bi-calculator me-2"></i> Comisiones
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">

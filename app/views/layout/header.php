<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VentasPlus - Sistema de Gestión de Comisiones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
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
        .table td, .table th {
            padding: 10px;
        }
        .table-container {
            padding: 15px;
            height: auto;
            overflow-y: auto;
        }
        .table-container .table {
            margin-bottom: 0;
        }
        .dataTables_wrapper {
            position: relative;
        }
        .dataTables_length,
        .dataTables_filter {
            margin-bottom: 15px;
        }
        .dataTables_info,
        .dataTables_paginate {
            margin-top: 15px;
        }
        .dataTables_scrollBody {
            height: 400px !important;
        }
        
        /* Sidebar Collapsible Styles */
        .sidebar {
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 60px;
        }
        
        .sidebar.collapsed .nav-text {
            display: none;
        }
        
        .sidebar.collapsed #sidebar-title {
            display: none;
        }
        
        .sidebar.collapsed .nav-link {
            text-align: center;
            padding: 12px 8px;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }
        
        .sidebar.collapsed .nav-text {
            display: none;
        }
        
        .sidebar .border-top {
            border-color: #dee2e6 !important;
        }
        
        .sidebar .btn {
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed .btn .nav-text {
            display: none;
        }
        
        .sidebar.collapsed .btn {
            padding: 8px;
            text-align: center;
        }
        
        #main-content {
            transition: all 0.3s ease;
        }
        
        .main-content {
            min-height: 100vh;
        }
        
        /* Cuando el sidebar está colapsado, el contenido principal se expande */
        .sidebar.collapsed {
            flex: 0 0 60px;
            max-width: 60px;
        }
        
        .sidebar.collapsed ~ #main-content {
            flex: 0 0 calc(100% - 60px);
            max-width: calc(100% - 60px);
        }
        
        /* Ajuste para el contenedor principal */
        .container-fluid .row {
            transition: all 0.3s ease;
        }
        
        /* Asegurar que el contenido principal ocupe el espacio disponible */
        #main-content {
            flex: 1;
            transition: all 0.3s ease;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            #main-content {
                margin-left: 0;
            }
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0" id="sidebar">
                <div class="sidebar d-flex flex-column">
                    <div class="p-3">
                        <h4 class="text-dark mb-0">
                            <i class="bi bi-graph-up-arrow"></i> <span id="sidebar-title">VentasPlus</span>
                        </h4>
                    </div>
                    <nav class="nav flex-column flex-grow-1">
                        <a class="nav-link" href="index.php?controller=Dashboard&action=index">
                            <i class="bi bi-house me-2"></i> <span class="nav-text">Dashboard</span>
                        </a>
                        <a class="nav-link" href="index.php?controller=Ventas&action=index">
                            <i class="bi bi-upload me-2"></i> <span class="nav-text">Cargar Ventas</span>
                        </a>
                        <a class="nav-link" href="index.php?controller=Devoluciones&action=index">
                            <i class="bi bi-arrow-return-left me-2"></i> <span class="nav-text">Cargar Devoluciones</span>
                        </a>
                        <a class="nav-link" href="index.php?controller=Vendedores&action=index">
                            <i class="bi bi-people me-2"></i> <span class="nav-text">Gestionar Vendedores</span>
                        </a>
                        <a class="nav-link" href="index.php?controller=Comisiones&action=index">
                            <i class="bi bi-calculator me-2"></i> <span class="nav-text">Comisiones</span>
                        </a>
                    </nav>
                    <div class="p-3 border-top">
                        <button class="btn btn-sm btn-outline-secondary w-100 d-none d-md-block" id="sidebar-toggle-desktop" title="Colapsar/Expandir menú">
                            <i class="bi bi-chevron-left"></i> <span class="nav-text">Colapsar</span>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebar-toggle-mobile-close">
                            <i class="bi bi-x"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9 col-lg-10" id="main-content">
                <div class="d-flex justify-content-between align-items-center py-2 mb-3 d-md-none">
                    <button class="btn btn-outline-secondary" id="sidebar-toggle-mobile">
                        <i class="bi bi-list"></i>
                    </button>
                    <h5 class="mb-0">VentasPlus</h5>
                    <div></div>
                </div>
                <div class="main-content p-4">

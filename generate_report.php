<?php
ob_start(); // Start output buffering

$page_title = 'Generate Report';
require_once('includes/load.php');

// Check for required page access level
page_require_level(1);

// Fetch all locations
$all_locations = find_all_assoc('location');

// Fetch all categories
$all_categories = find_all('categories');

// Set records per page
$records_per_page = 9;

// Get current page or set default
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the query
$offset = ($current_page - 1) * $records_per_page;

// Fetch the report history from the database with pagination
$report_history = fetch_report_history_paginated($offset, $records_per_page);

// Count total records for pagination
$total_records = count_report_history();

// Calculate total pages
$total_pages = ceil($total_records / $records_per_page);

// Initialize PDF library
require('fpdf/fpdf.php');

// If the form is submitted for location-based report
if (isset($_POST['generate_location_report'])) {
    $location_id = $_POST['location'];
    $report_data = [];
    $report_title = 'Inventory Report in All Locations'; // Default title for all locations
    $location_name = "All Locations"; // Default location name

    // Query for data based on selected location
    if ($location_id == 'all') {
        // Fetch all categories
        $categories = find_all('categories');
        
        // For each category, fetch all products in that category
        foreach ($categories as $category) {
            $category_id = $category['id'];
            // Get all products in this category
            $products = find_by_category_id('products', $category_id);
            
            // Store products with their stock information
            $category_products = [];
            foreach ($products as $product) {
                // Get stock info for this product (from all locations)
                $stock_items = find_by_product_id('stock', $product['id']);
                
                if (!empty($stock_items)) {
                    // Group stock items by product
                    $consolidated_item = [
                        'product_name' => $product['name'],
                        'property_numbers' => [],
                        'date_issued' => [],
                        'quantities' => [],
                        'statuses' => []
                    ];
                    
                    // Collect all property numbers for this product
                    foreach ($stock_items as $stock) {
                        // Get status name
                        $status = find_by_id('status', $stock['status_id']);
                        $status_name = $status ? $status['name'] : 'N/A';
                        
                        $consolidated_item['property_numbers'][] = $stock['stock_number'];
                        // Use product date as fallback for date_issued since stock doesn't have date_added
                        $consolidated_item['date_issued'][] = isset($product['date']) ? date('m/d/Y', strtotime($product['date'])) : 'N/A';
                        // Use product quantity as fallback since stock doesn't have quantity
                        $consolidated_item['quantities'][] = isset($product['quantity']) ? $product['quantity'] : '1';
                        $consolidated_item['statuses'][] = $status_name;
                    }
                    
                    $category_products[] = $consolidated_item;
                }
            }
            
            // Only add categories that have products
            if (!empty($category_products)) {
                $report_data[$category['name']] = $category_products;
            }
        }
    } else {
        // Fetch the selected location name for the title
        $location = find_by_id('location', $location_id);
        $location_name = $location ? ucfirst($location['name']) : 'Unknown Location';
        $report_title = 'Inventory Report in ' . $location_name;
        
        // Fetch all categories
        $categories = find_all('categories');
        
        // For each category, fetch all products in that category for the specific location
        foreach ($categories as $category) {
            $category_id = $category['id'];
            // Get all products in this category
            $products = find_by_category_id('products', $category_id);
            
            // Store products with their stock information
            $category_products = [];
            foreach ($products as $product) {
                // Get stock info for this product at the specific location
                $stock_items = find_by_product_and_location('stock', $product['id'], $location_id);
                
                if (!empty($stock_items)) {
                    // Group stock items by product
                    $consolidated_item = [
                        'product_name' => $product['name'],
                        'property_numbers' => [],
                        'date_issued' => [],
                        'quantities' => [],
                        'statuses' => []
                    ];
                    
                    // Collect all property numbers for this product
                    foreach ($stock_items as $stock) {
                        // Get status name
                        $status = find_by_id('status', $stock['status_id']);
                        $status_name = $status ? $status['name'] : 'N/A';
                        
                        $consolidated_item['property_numbers'][] = $stock['stock_number'];
                        // Use product date as fallback for date_added since stock doesn't have date_added
                        $consolidated_item['date_issued'][] = isset($product['date']) ? date('m/d/Y', strtotime($product['date'])) : 'N/A';
                        // Use product quantity as fallback since stock doesn't have quantity
                        $consolidated_item['quantities'][] = isset($product['quantity']) ? $product['quantity'] : '1';
                        $consolidated_item['statuses'][] = $status_name;
                    }
                    
                    $category_products[] = $consolidated_item;
                }
            }
            
            // Only add categories that have products
            if (!empty($category_products)) {
                $report_data[$category['name']] = $category_products;
            }
        }
    }

    // Check if any data was found
    $found_data = false;
    foreach ($report_data as $category => $items) {
        if (!empty($items)) {
            $found_data = true;
            break;
        }
    }

    if (!$found_data) {
        $session->msg('d', 'No data found for the selected location.');
        redirect('generate_report.php');
    }

    // Create the PDF if data exists
    if ($found_data) {
        generatePDF($report_data, $location_name);
    }
}

// If the form is submitted for category-based report
if (isset($_POST['generate_category_report'])) {
    $category_id = $_POST['category'];
    $report_data = [];
    $report_title = 'Inventory Report for All Categories'; // Default title
    $category_name = "All Categories"; // Default category name

    // Query for data based on selected category
    if ($category_id == 'all') {
        // Get all categories
        $categories = find_all('categories');
        
        foreach ($categories as $category) {
            $cat_id = $category['id'];
            $cat_name = $category['name'];
            
            // Get all products in this category
            $products = find_by_category_id('products', $cat_id);
            
            // Initialize category stock summary
            $category_summary = [];
            
            foreach ($products as $product) {
                // Get all stock for this product across all locations
                $stock_items = find_by_product_id('stock', $product['id']);
                
                if (!empty($stock_items)) {
                    // Count total items (use count instead of quantities since stock table doesn't have quantity)
                    $total_quantity = count($stock_items);
                    $status_counts = [];
                    
                    foreach ($stock_items as $stock) {
                        // Count by status
                        $status_id = $stock['status_id'];
                        if (!isset($status_counts[$status_id])) {
                            $status_counts[$status_id] = 0;
                        }
                        $status_counts[$status_id]++;
                    }
                    
                    // Format status counts
                    $status_summary = [];
                    foreach ($status_counts as $status_id => $count) {
                        $status = find_by_id('status', $status_id);
                        $status_name = $status ? $status['name'] : 'Unknown';
                        $status_summary[] = "$status_name: $count";
                    }
                    
                    // Add product to category summary
                    $category_summary[] = [
                        'product_name' => $product['name'],
                        'total_quantity' => $total_quantity,
                        'status_summary' => implode(', ', $status_summary),
                        'quantity' => $product['quantity'] ? $product['quantity'] : $total_quantity
                    ];
                }
            }
            
            // Only add categories that have products with stock
            if (!empty($category_summary)) {
                $report_data[$cat_name] = $category_summary;
            }
        }
    } else {
        // Get selected category name
        $category = find_by_id('categories', $category_id);
        $category_name = $category ? $category['name'] : 'Unknown Category';
        $report_title = 'Inventory Report for ' . $category_name;
        
        // Get all products in this category
        $products = find_by_category_id('products', $category_id);
        
        // Initialize category stock summary
        $category_summary = [];
        
        foreach ($products as $product) {
            // Get all stock for this product across all locations
            $stock_items = find_by_product_id('stock', $product['id']);
            
            if (!empty($stock_items)) {
                // Count total items (use count instead of quantities)
                $total_quantity = count($stock_items);
                $status_counts = [];
                $location_counts = [];
                
                foreach ($stock_items as $stock) {
                    // Count by status
                    $status_id = $stock['status_id'];
                    if (!isset($status_counts[$status_id])) {
                        $status_counts[$status_id] = 0;
                    }
                    $status_counts[$status_id]++;
                    
                    // Count by location
                    $location_id = $stock['location_id'];
                    if (!isset($location_counts[$location_id])) {
                        $location_counts[$location_id] = 0;
                    }
                    $location_counts[$location_id]++;
                }
                
                // Format status counts
                $status_summary = [];
                foreach ($status_counts as $status_id => $count) {
                    $status = find_by_id('status', $status_id);
                    $status_name = $status ? $status['name'] : 'Unknown';
                    $status_summary[] = "$status_name: $count";
                }
                
                // Format location counts
                $location_summary = [];
                foreach ($location_counts as $loc_id => $count) {
                    $location = find_by_id('location', $loc_id);
                    $location_name = $location ? $location['name'] : 'Unknown';
                    $location_summary[] = "$location_name: $count";
                }
                
                // Add product to category summary
                $category_summary[] = [
                    'product_name' => $product['name'],
                    'total_quantity' => $total_quantity,
                    'status_summary' => implode(', ', $status_summary),
                    'location_summary' => implode(', ', $location_summary),
                    'quantity' => $product['quantity'] ? $product['quantity'] : $total_quantity
                ];
            }
        }
        
        // Add the category data to the report
        if (!empty($category_summary)) {
            $report_data[$category_name] = $category_summary;
        }
    }

    // Check if any data was found
    $found_data = false;
    foreach ($report_data as $category => $items) {
        if (!empty($items)) {
            $found_data = true;
            break;
        }
    }

    if (!$found_data) {
        $session->msg('d', 'No data found for the selected category.');
        redirect('generate_report.php');
    }

    // Create the PDF if data exists
    if ($found_data) {
        generateCategoryPDF($report_data, $category_name);
    }
}

// Function to generate location-based PDF report
function generatePDF($report_data, $location_name) {
    ob_end_clean(); // Clear any output that might have been sent before PDF
    
    // Create PDF with portrait orientation
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    
    // Set some default values for styling
    $primary_color = [125, 125, 125]; // Nice blue color
    $secondary_color = [236, 240, 241]; // Light gray background
    $text_color = [44, 62, 80]; // Dark blue-gray
    
    // Header section
    addReportHeader($pdf, $location_name, $primary_color, $text_color);
    
    // Loop through each category and its items
    $item_total = 0;
    
    foreach ($report_data as $category_name => $items) {
        if (empty($items)) continue;
        
        // Category header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor($secondary_color[0], $secondary_color[1], $secondary_color[2]);
        $pdf->Cell(0, 8, '   ' . strtoupper($category_name), 1, 1, 'L', true);
        
        // Set colors for table header
        $pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
        $pdf->SetTextColor(255, 255, 255); // White text
        
        // Table header
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(75, 7, 'DESCRIPTION', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'PROPERTY NO.', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'DATE ISSUED', 1, 0, 'C', true);
        $pdf->Cell(15, 7, 'QTY', 1, 0, 'C', true);
        $pdf->Cell(45, 7, 'STATUS', 1, 1, 'C', true);
        
        // Reset text color for table data
        $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);
        $pdf->SetFont('Arial', '', 9);
        
        // Print items in this category
        $category_qty = 0;
        foreach ($items as $item) {
            // First row - print product name and first property number
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(75, 7, ' ' . $item['product_name'], 1, 0, 'L');
            $pdf->SetFont('Arial', '', 9);
            
            // Check if there are any property numbers
            if (!empty($item['property_numbers'])) {
                // Determine if we need multiple rows
                $total_qty = 0;
                
                for ($i = 0; $i < count($item['property_numbers']); $i++) {
                    // First property number on same line as product name
                    if ($i == 0) {
                        $pdf->Cell(30, 7, $item['property_numbers'][$i], 1, 0, 'C');
                        $pdf->Cell(25, 7, $item['date_issued'][$i], 1, 0, 'C');
                        $pdf->Cell(15, 7, $item['quantities'][$i], 1, 0, 'C');
                        $pdf->Cell(45, 7, $item['statuses'][$i], 1, 1, 'C');
                    } else {
                        // Additional rows for remaining property numbers
                        $pdf->Cell(75, 7, '', 1, 0, 'C'); // Empty cell for product name
                        $pdf->Cell(30, 7, $item['property_numbers'][$i], 1, 0, 'C');
                        $pdf->Cell(25, 7, $item['date_issued'][$i], 1, 0, 'C');
                        $pdf->Cell(15, 7, $item['quantities'][$i], 1, 0, 'C');
                        $pdf->Cell(45, 7, $item['statuses'][$i], 1, 1, 'C');
                    }
                    
                    $total_qty += intval($item['quantities'][$i]);
                }
                
                $category_qty += $total_qty;
            } else {
                // No property numbers - create empty row
                $pdf->Cell(30, 7, 'N/A', 1, 0, 'C');
                $pdf->Cell(25, 7, 'N/A', 1, 0, 'C');
                $pdf->Cell(15, 7, '0', 1, 0, 'C');
                $pdf->Cell(45, 7, 'N/A', 1, 1, 'C');
            }
        }
        
        // Add category total
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(130, 7, 'Total Items in Category', 1, 0, 'R');
        $pdf->Cell(15, 7, $category_qty, 1, 0, 'C');
        $pdf->Cell(45, 7, '', 1, 1, 'L');
        
        $item_total += $category_qty;
        $pdf->Ln(3); // Add some space between categories
    }
    
    // Grand total
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(130, 8, 'GRAND TOTAL', 0, 0, 'R');
    $pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(15, 8, $item_total, 1, 0, 'C', true);
    $pdf->Cell(45, 8, '', 0, 1);
    
    // Reset text color
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);
    
    // Add signature section at the bottom
    addSignatureSection($pdf);
    
    // Add footer with page numbers
    $pdf->SetY(-15);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo() . ' of {nb}', 0, 0, 'C');
    
    // Generate a filename for saving
    $filename = 'reports/Inventory_Report_Location_' . date('Y-m-d_His') . '.pdf';
    
    // Make sure the reports directory exists
    if (!is_dir('reports')) {
        mkdir('reports', 0755, true);
    }
    
    // Save the PDF to the server
    $pdf->Output('F', $filename);
    
    // Record this report in the database
    $location_id = isset($_POST['location']) ? $_POST['location'] : 'all';
    $user_id = $_SESSION['user_id'];
    insert_report_history($user_id, $location_id, 'Location Report', $filename);
    
    // Output the PDF
    $pdf->Output('I', 'Inventory_Report_Location_' . date('Y-m-d') . '.pdf');
    exit;
}

// Function to generate category-based PDF
function generateCategoryPDF($report_data, $category_name) {
    ob_end_clean();
    
    // Create PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AliasNbPages(); // For page numbering
    $pdf->AddPage();
    
    // Set colors
    $primary_color = [125, 125, 125];// Slightly different blue
    $secondary_color = [236, 240, 241]; // Light gray background
    $text_color = [44, 62, 80]; // Dark blue-gray
    
    // Header section with category name
    addReportHeader($pdf, $category_name, $primary_color, $text_color, true);
    
    // Loop through each category
    $grand_total = 0;
    
    foreach ($report_data as $category_name => $items) {
        if (empty($items)) continue;
        
        // Category header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor($secondary_color[0], $secondary_color[1], $secondary_color[2]);
        $pdf->Cell(0, 8, '   ' . strtoupper($category_name), 1, 1, 'L', true);
        
        // Table header
        $pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
        $pdf->SetTextColor(255, 255, 255); // White text
        $pdf->SetFont('Arial', 'B', 9);
        
        // Determine columns based on report type
        if (isset($items[0]['location_summary'])) {
            // Location distribution header
            $pdf->Cell(90, 7, 'PRODUCT', 1, 0, 'C', true);
            $pdf->Cell(20, 7, 'QTY', 1, 0, 'C', true);
            $pdf->Cell(80, 7, 'LOCATION DISTRIBUTION', 1, 1, 'C', true);
        } else {
            // Status distribution header
            $pdf->Cell(90, 7, 'PRODUCT', 1, 0, 'C', true);
            $pdf->Cell(20, 7, 'QTY', 1, 0, 'C', true);
            $pdf->Cell(80, 7, 'STATUS DISTRIBUTION', 1, 1, 'C', true);
        }
        
        // Reset text color for data
        $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);
        $pdf->SetFont('Arial', '', 9);
        
        // Print items
        $category_total = 0;
        foreach ($items as $item) {
            // Calculate row height based on distribution text
            $dist_text = isset($item['location_summary']) ? $item['location_summary'] : $item['status_summary'];
            $line_count = ceil(strlen($dist_text) / 40); // Approx chars per line
            $row_height = max(7, $line_count * 5);
            
            // Print product info
            $pdf->Cell(90, $row_height, ' ' . $item['product_name'], 1, 0, 'L');
            $pdf->Cell(20, $row_height, $item['quantity'], 1, 0, 'C');
            
            // Print distribution info with multi-cell
            $pdf->MultiCell(80, $row_height / $line_count, $dist_text, 1, 'L');
            
            // Add to category total
            $category_total += $item['total_quantity'];
        }
        
        // Category total
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(90, 7, 'Total in Category', 1, 0, 'R');
        $pdf->Cell(20, 7, $category_total, 1, 0, 'C');
        $pdf->Cell(80, 7, '', 1, 1);
        
        $grand_total += $category_total;
        $pdf->Ln(3);
    }
    
    // Grand total
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 8, 'GRAND TOTAL', 0, 0, 'R');
    $pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(20, 8, $grand_total, 1, 0, 'C', true);
    $pdf->Cell(80, 8, '', 0, 1);
    
    // Reset text color
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);
    
    // Add signature section
    addSignatureSection($pdf);
    
    // Add footer with page numbers
    $pdf->SetY(-15);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo() . ' of {nb}', 0, 0, 'C');
    
    // Generate filename and save
    $filename = 'reports/Inventory_Report_Category_' . date('Y-m-d_His') . '.pdf';
    
    if (!is_dir('reports')) {
        mkdir('reports', 0755, true);
    }
    
    $pdf->Output('F', $filename);
    
    // Record in database
    $category_id = isset($_POST['category']) ? $_POST['category'] : 'all';
    $user_id = $_SESSION['user_id'];
    insert_report_history($user_id, 'all', 'Category Report', $filename);
    
    $pdf->Output('I', 'Inventory_Report_Category_' . date('Y-m-d') . '.pdf');
    exit;
}

// Function to add a standardized header to reports
function addReportHeader($pdf, $title_text, $primary_color, $text_color, $is_category = false) {
    // Set text color for header
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);
    
    // Add logo or icon (you can replace with your organization's logo)
    $pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
    $pdf->Rect(10, 10, 190, 25, 'F');
    
    // Add title text
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 255, 255);
    if ($is_category) {
        $pdf->Text(15, 20, 'INVENTORY REPORT BY CATEGORY');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Text(15, 28, strtoupper($title_text));
    } else {
        $pdf->Text(15, 20, strtoupper($title_text) . ' PROPERTY INVENTORY');
    }
    
    // Add date
    $pdf->SetFont('Arial', '', 10);
    $pdf->Text(150, 28, 'As of: ' . date('m/d/Y'));
    
    // Reset position after header
    $pdf->SetY(40);
    
    // Reset text color
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);
}

// Function to add signature section
function addSignatureSection($pdf) {
    $pdf->Ln(15);
    
    // Create signature layout
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(95, 5, 'PREPARED BY:', 0, 0, 'L');
    $pdf->Ln(10);
    
    // Signature lines
    $pdf->SetFont('Arial', 'BU', 10);
    $pdf->Cell(95, 5, 'MRS. LEAH Y. BAJE', 0, 0, 'L');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(95, 5, 'PROPERTY CUSTODIAN', 0, 0, 'L');
    
    // Date and time generated
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, 'Report generated on ' . date('m/d/Y h:i:s A'), 0, 0, 'R');
}

?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-12">
        <?php echo display_msg($msg); ?>
    </div>

    <div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header cont-head justify-content-between d-flex align-items-center">
                <h5 class="mb-0">
                <i class="bi bi-clock-history me-2 symbol"></i> Reports
                </h5>
                <button class="btn btn-sm rounded-pill secondary-btn" onclick="openReportModal()">Generate Report</button> 
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Report ID</th>
                                <th scope="col">Generated By</th>
                                <th scope="col">Location</th>
                                <th scope="col">Report Type</th>
                                <th scope="col">Date Generated</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_history as $report): ?>
                                <tr>
                                    <td><?= count_id(); ?></td>
                                    <td><?php echo get_user_by_id($report['generated_by'])['username']; ?></td>
                                    <td><?php echo get_location_name($report['location_id']); ?></td>
                                    <td><?php echo $report['report_type']; ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($report['generated_at'])); ?></td>
                                    <td>
                                        <?php if (!empty($report['file_path'])): ?>
                                            <a href="<?php echo $report['file_path']; ?>" class="btn secondary-btn btn-sm">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($report_history)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No reports available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous button -->
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Page numbers -->
                        <?php
                        // Define how many page numbers to show
                        $max_links = 5;
                        $start_page = max(1, $current_page - floor($max_links / 2));
                        $end_page = min($total_pages, $start_page + $max_links - 1);
                        
                        // Adjust start page if needed
                        if ($end_page - $start_page + 1 < $max_links) {
                            $start_page = max(1, $end_page - $max_links + 1);
                        }
                        
                        // First page link if not in range
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><a class="page-link">...</a></li>
                            <?php endif; 
                        endif;
                        
                        // Page numbers
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor;
                        
                        // Last page link if not in range
                        if ($end_page < $total_pages): 
                            if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><a class="page-link">...</a></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Next button -->
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                

            </div>
        </div>
    </div>
</div>



<!-- Generate Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-3">
      <div class="modal-header cont-head">
        <div class="d-flex align-items-center">
          <i class="bi bi-file-earmark-bar-graph-fill me-2 symbol"></i>
          <strong>Generate Reports</strong>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <!-- Location-based Report -->
          <div class="col-md-6">
            <div class="card h-100 shadow-sm">
              <div class="card-header bg-light">
                <strong>Generate Report by Location</strong>
              </div>
              <div class="card-body">
                <form method="post" action="generate_report.php">
                  <div class="mb-3">
                    <label for="location" class="form-label">Choose Location</label>
                    <select name="location" id="location" class="form-select">
                      <option value="all">All Locations</option>
                      <?php foreach ($all_locations as $loc): ?>
                        <option value="<?php echo $loc['id']; ?>"><?php echo remove_junk(ucfirst($loc['name'])); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <button type="submit" name="generate_location_report" class="btn secondary-btn w-100">
                    <i class="bi bi-geo-alt-fill me-1"></i> Generate Location Report
                  </button>
                </form>
              </div>
            </div>
          </div>
                                
          <!-- Category-based Report -->
          <div class="col-md-6">
            <div class="card h-100 shadow-sm">
              <div class="card-header bg-light">
                <strong>Generate Report by Category</strong>
              </div>
              <div class="card-body">
                <form method="post" action="generate_report.php">
                  <div class="mb-3">
                    <label for="category" class="form-label">Choose Category</label>
                    <select name="category" id="category" class="form-select">
                      <option value="all">All Categories</option>
                      <?php foreach ($all_categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo remove_junk(ucfirst($cat['name'])); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <button type="submit" name="generate_category_report" class="btn secondary-btn w-100">
                    <i class="bi bi-tags-fill me-1"></i> Generate Category Report
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>  

<script>
function openReportModal() {
    const modal = new bootstrap.Modal(document.getElementById("reportModal"));
    modal.show();
}

var modalReport = document.getElementById("reportModal");
var spanReport = document.getElementsByClassName("close")[0];
</script>


<?php include_once('layouts/footer.php'); ?>
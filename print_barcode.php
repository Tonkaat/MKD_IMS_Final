<?php
$page_title = 'Barcodes';
require_once('includes/load.php');

// Fetch all products with status_id = 1 (consumable)
$sql = "SELECT id, name, barcode FROM products WHERE categorie_id = '12'";
$result = $db->query($sql);
$products = [];

while ($row = $db->fetch_assoc($result)) {
    // If barcode is missing, generate one and update DB
    if (empty($row['barcode'])) {
        $barcode = uniqid(); // Or time() . rand(100, 999) for more variety
        $updateSql = "UPDATE products SET barcode = '{$barcode}' WHERE id = '{$row['id']}'";
        $db->query($updateSql);
        $row['barcode'] = $barcode;
    }

    $products[] = $row;
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="">
    <div class="d-flex justify-content-between align-items-center mb-3 text-primary">
        <h2 class="mb-0 fw-bold" style="color: var(--primary)">
            <i class="bi bi-upc-scan me-2"></i>Generated Product Barcodes
        </h2>
    </div>
    
    <!-- Search and Selection Controls -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row">
                <!-- Search Box -->
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="input-group">
                        <span class="input-group-text cont-head">
                            <i class="bi bi-search symbol"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search">
                        <button class="btn main-btn" type="button" onclick="clearSearch()">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                </div>
                
                <!-- Selection Controls -->
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2">
                        <button id="selectAllBtn" class="btn secondary-btn">
                            <i class="bi bi-check-all"></i> Select All
                        </button>
                        <button id="unselectAllBtn" class="btn main-btn">
                            <i class="bi bi-x"></i> Unselect All
                        </button>
                        <button id="printSelectedBtn" class="btn secondary-btn " disabled>
                            <i class="bi bi-printer"></i> Print Selected <span id="selectedCount">(0)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Barcode Container with Scrolling -->
    <div class="card shadow-sm">
        <div class="card-body p-3">
            <div class="barcode-scroll-container" style="overflow-y: auto;">
                <div class="row g-3" id="barcodeContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 col-sm-6 barcode-item" data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                            <div class="border rounded p-3 text-center shadow-sm h-100 barcode-box position-relative">
                                <!-- Selection Checkbox -->
                                <div class="form-check position-absolute top-0 start-0 m-2">
                                    <input class="form-check-input barcode-selector" type="checkbox" 
                                           id="check-<?= $product['id'] ?>" 
                                           data-id="<?= $product['id'] ?>"
                                           data-name="<?= htmlspecialchars($product['name']) ?>"
                                           data-barcode="<?= htmlspecialchars($product['barcode']) ?>">
                                </div>
                                
                                <h5 class="mb-2 mt-1"><?= htmlspecialchars($product['name']) ?></h5>
                                <?php if (!empty($product['barcode'])): ?>
                                    <div class="barcode-container text-center">
                                        <svg class="barcode mb-2" id="barcode-<?= $product['id'] ?>"
                                             data-barcode="<?= htmlspecialchars($product['barcode']) ?>"></svg>
                                        <small class="text-muted d-block mb-3"><?= htmlspecialchars($product['barcode']) ?></small>
                                    </div>
                                    
                                    <!-- Individual Print Button -->
                                    <!-- <button class="btn btn-sm  print-single-barcode" 
                                            data-id="<?= $product['id'] ?>"
                                            data-name="<?= htmlspecialchars($product['name']) ?>"
                                            data-barcode="<?= htmlspecialchars($product['barcode']) ?>">
                                        <i class="bi bi-printer"></i> Print This Barcode
                                    </button> -->
                                <?php else: ?>
                                    <p class="text-danger">No barcode available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single Barcode Print Modal -->
<div class="modal fade" id="singleBarcodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Print Barcode</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="singleBarcodePrint">
                <h4 id="modalProductName"></h4>
                <div id="modalBarcodeContainer"></div>
                <small class="text-muted" id="modalBarcodeText"></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printSingleBarcode()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Multiple Barcodes Print Modal -->
<div class="modal fade" id="multipleBarcodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Print Selected Barcodes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="multipleBarcodesPrint">
                <div id="barcodeGrid" class="barcode-print-grid">
                    <!-- Barcodes will be inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn main-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn secondary-btn" onclick="printMultipleBarcodes()">
                    <i class="bi bi-printer"></i> Print Selected
                </button>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    if (typeof JsBarcode === 'undefined') {
      console.error("JsBarcode library not loaded!");
      return;
    }
   
    // Generate all barcodes with smaller size
    document.querySelectorAll("svg.barcode").forEach(function (svg) {
      try {
        const barcodeValue = svg.getAttribute("data-barcode");
        if (!barcodeValue) {
          console.error("No barcode value for:", svg);
          return;
        }
        JsBarcode(svg, barcodeValue, {
          format: "CODE128",
          displayValue: true,
          fontSize: 12,
          height: 50,
          width: 1.5,
          margin: 5
        });
      } catch (e) {
        console.error("Error generating barcode:", e);
      }
    });
    
    // Setup search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', function() {
      const searchText = this.value.toLowerCase().trim();
      filterProducts(searchText);
    });
    
    // Setup single barcode printing
    const singleModal = new bootstrap.Modal(document.getElementById('singleBarcodeModal'));
    const multipleModal = new bootstrap.Modal(document.getElementById('multipleBarcodeModal'));
    
    document.querySelectorAll('.print-single-barcode').forEach(function(button) {
      button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const barcode = this.getAttribute('data-barcode');
        
        document.getElementById('modalProductName').textContent = name;
        document.getElementById('modalBarcodeText').textContent = barcode;
        
        const container = document.getElementById('modalBarcodeContainer');
        container.innerHTML = '';
        
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.classList.add("barcode");
        container.appendChild(svg);
        
        JsBarcode(svg, barcode, {
          format: "CODE128",
          displayValue: true,
          fontSize: 12,
          height: 60,
          width: 1.5,
          margin: 10
        });
        
        singleModal.show();
      });
    });
    
    // Setup barcode selection functionality
    const selectAllBtn = document.getElementById('selectAllBtn');
    const unselectAllBtn = document.getElementById('unselectAllBtn');
    const printSelectedBtn = document.getElementById('printSelectedBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    // Select All button
    selectAllBtn.addEventListener('click', function() {
      const checkboxes = document.querySelectorAll('.barcode-selector');
      checkboxes.forEach(function(checkbox) {
        if (!checkbox.closest('.barcode-item').style.display || 
            checkbox.closest('.barcode-item').style.display !== 'none') {
          checkbox.checked = true;
        }
      });
      updateSelectedCount();
    });
    
    // Unselect All button
    unselectAllBtn.addEventListener('click', function() {
      const checkboxes = document.querySelectorAll('.barcode-selector');
      checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;
      });
      updateSelectedCount();
    });
    
    // Print Selected button
    printSelectedBtn.addEventListener('click', function() {
      const selectedBarcodes = getSelectedBarcodes();
      if (selectedBarcodes.length > 0) {
        renderMultipleBarcodes(selectedBarcodes);
        multipleModal.show();
      }
    });
    
    // Handle checkbox changes
    document.addEventListener('change', function(e) {
      if (e.target && e.target.classList.contains('barcode-selector')) {
        updateSelectedCount();
      }
    });
    
    // Initial update of selected count
    updateSelectedCount();
  });
  
  function updateSelectedCount() {
    const selectedBarcodes = getSelectedBarcodes();
    const count = selectedBarcodes.length;
    const printSelectedBtn = document.getElementById('printSelectedBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    selectedCountSpan.textContent = `(${count})`;
    printSelectedBtn.disabled = count === 0;
  }
  
  function getSelectedBarcodes() {
    const selected = [];
    document.querySelectorAll('.barcode-selector:checked').forEach(function(checkbox) {
      selected.push({
        id: checkbox.getAttribute('data-id'),
        name: checkbox.getAttribute('data-name'),
        barcode: checkbox.getAttribute('data-barcode')
      });
    });
    return selected;
  }
  
  function renderMultipleBarcodes(barcodes) {
    const container = document.getElementById('barcodeGrid');
    container.innerHTML = '';
    
    barcodes.forEach(function(item) {
      const barcodeBox = document.createElement('div');
      barcodeBox.className = 'barcode-label';
      
      // Create product name element
      const nameElem = document.createElement('div');
      nameElem.className = 'barcode-product-name';
      nameElem.textContent = item.name;
      barcodeBox.appendChild(nameElem);
      
      // Create SVG for barcode
      const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
      svg.classList.add("print-barcode");
      barcodeBox.appendChild(svg);
      
      // Create barcode number element
      const codeElem = document.createElement('div');
      codeElem.className = 'barcode-number';
      codeElem.textContent = item.barcode;
      barcodeBox.appendChild(codeElem);
      
      container.appendChild(barcodeBox);
      
      // Generate barcode
      JsBarcode(svg, item.barcode, {
        format: "CODE128",
        displayValue: false, // Don't display value under barcode since we add it separately
        fontSize: 8,
        height: 30,
        width: 1,
        margin: 0
      });
    });
  }
  
  function filterProducts(searchText) {
    const items = document.querySelectorAll('.barcode-item');
    let found = false;
    
    items.forEach(function(item) {
      const name = item.getAttribute('data-name');
      if (name.includes(searchText)) {
        item.style.display = '';
        found = true;
      } else {
        item.style.display = 'none';
      }
    });
    
    if (!found) {
      const container = document.getElementById('barcodeContainer');
      if (!document.getElementById('no-results')) {
        const noResults = document.createElement('div');
        noResults.id = 'no-results';
        noResults.className = 'col-12 text-center py-5';
        noResults.innerHTML = '<h4 class="text-muted"><i class="bi bi-search"></i> No products found</h4>';
        container.appendChild(noResults);
      }
    } else {
      const noResults = document.getElementById('no-results');
      if (noResults) {
        noResults.remove();
      }
    }
    
    // Update selection count after filtering
    updateSelectedCount();
  }
  
  function clearSearch() {
    document.getElementById('searchInput').value = '';
    filterProducts('');
  }
  
  function printSingleBarcode() {
    const content = document.getElementById('singleBarcodePrint').innerHTML;
    printBarcodeContent(content);
  }
  
  function printMultipleBarcodes() {
    const content = document.getElementById('multipleBarcodesPrint').innerHTML;
    printBarcodeContent(content);
  }
  
  function printBarcodeContent(content) {
    
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Print Barcodes</title>
        <style>
          body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
          }
          
          /* For single barcode printing */
          #modalBarcodeContainer svg {
            width: 100%;
            max-width: 200px;
            margin: 10px auto;
            display: block;
          }
          
          /* For multiple barcodes printing */
          .barcode-print-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.1rem;
            page-break-inside: auto;
          }
          
          .barcode-label {
            border: 1px dashed #ccc;
            padding: 5px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            page-break-inside: avoid;
          }
          
          .barcode-product-name {
            font-size: 9px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
          }
          
          .barcode-number {
            font-size: 8px;
            color: #666;
          }
          
          .print-barcode {
            width: 100%;
            height: 30px;
          }
          
          @media print {
            @page {
              size: A4;
              margin: 0.5cm;
            }
            
            body {
              width: 100%;
            }
            
            .barcode-print-grid {
              width: 100%;
            }
          }
        </style>
      </head>
      <body>
        ${content}
        <script>
          window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 500);
          };
         </` + `script>
      </body>
      </html>
    `);
    
    printWindow.document.close();
  }
</script>
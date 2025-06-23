<?php

include_once 'config.php'; // expects: $conn = new PDO('pgsql:host=…;dbname=…;user=…;password=…');

// Ensure user is logged in
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Pagination settings
$limit  = 10;
$page   = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// 1) Total count
$countSql  = "SELECT COUNT(*) FROM trades WHERE user_id = :user_id";
$countStmt = $conn->prepare($countSql);
$countStmt->execute(['user_id' => $user_id]);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = (int) ceil($totalRows / $limit);

// 2) Fetch paginated trades
$dataSql = <<<SQL
SELECT
    trade_id,
    trade_category,
    trade_type,
    asset,
    lot_size,
    entry_price,
    amount,
    trade_date,
    trading_results
FROM trades
WHERE user_id = :user_id
ORDER BY trade_date DESC
LIMIT :limit OFFSET :offset
SQL;

$dataStmt = $conn->prepare($dataSql);
$dataStmt->bindValue('user_id', $user_id, PDO::PARAM_INT);
$dataStmt->bindValue('limit',   $limit,   PDO::PARAM_INT);
$dataStmt->bindValue('offset',  $offset,  PDO::PARAM_INT);
$dataStmt->execute();

$trades = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Trades</title>
  <script>
    // Optional: Row‐click handler to inspect trade details
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('tr[data-trade]').forEach(row => {
        row.addEventListener('click', () => {
          const trade = JSON.parse(row.getAttribute('data-trade'));
          alert("Trade Details:\n" + JSON.stringify(trade, null, 2));
        });
      });
    });
  </script>
</head>
<body class="bg-gray-900 text-white p-4">

  <h1 class="text-2xl font-bold mb-4">Your Trades</h1>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm">
      <thead class="border-b border-gray-700">
        <tr>
          <?php
            $columns = [
              'Trade ID','Category','Type','Asset',
              'Lot Size','Entry Price','Amount','Trade Date','Result'
            ];
            foreach ($columns as $col) {
              echo "<th class=\"px-6 py-3 text-xs font-medium uppercase tracking-wider\">{$col}</th>";
            }
          ?>
        </tr>
      </thead>
      <tbody>
        <?php if (count($trades) > 0): ?>
          <?php foreach ($trades as $row): ?>
            <?php $tradeData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>
            <tr class="border-b border-gray-700 hover:bg-white cursor-pointer" data-trade='<?= $tradeData ?>'>
              <td class="px-6 py-4"><?= htmlspecialchars($row['trade_id']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['trade_category']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['trade_type']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['asset']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['lot_size']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['entry_price']) ?></td>
              <td class="px-6 py-4">$<?= htmlspecialchars($row['amount']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['trade_date']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['trading_results']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="py-4 px-6 text-center text-white">
              No trades found.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="mt-6 flex justify-center space-x-2">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page-1 ?>" class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600">Previous</a>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <?php if ($p === $page): ?>
        <span class="px-3 py-1 bg-blue-600 rounded"><?= $p ?></span>
      <?php else: ?>
        <a href="?page=<?= $p ?>" class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600"><?= $p ?></a>
      <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page+1 ?>" class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600">Next</a>
    <?php endif; ?>
  </div>

<!-- Binance-Style Modal (Resized, Smaller Fonts) -->
<div id="tradeModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-[#1a1a1a] text-white rounded-lg shadow-lg p-4 w-11/12 md:w-1/2 relative">
    <button id="modalClose" class="absolute top-2 right-2 text-gray-400 hover:text-white text-2xl">&times;</button>
    <!-- Top section: Amount & Status -->
    <div class="text-xl font-bold text-[#00ffb0] leading-tight" id="modalAmountDisplay">--</div>
    <div class="text-sm font-semibold text-[#00ff6a] mt-1" id="modalStatusDisplay">--</div>
    <!-- Divider -->
    <div class="border-t border-gray-600 my-3"></div>
    <!-- Transaction Details -->
    <div class="text-xs">
      <div class="mt-2">
        <span class="text-[#999]">Trade ID:</span>
        <span id="modalTradeId"></span>
      </div>
      <div class="mt-2">
        <span class="text-[#999]">Category / Type:</span>
        <span id="modalTradeCatType"></span>
      </div>
      <div class="mt-2">
        <span class="text-[#999]">Lot Size:</span>
        <span id="modalLotSize"></span>
      </div>
      <div class="mt-2">
        <span class="text-[#999]">Entry Price:</span>
        <span id="modalEntryPrice"></span>
      </div>
      <div class="mt-2">
        <span class="text-[#999]">Trade Date:</span>
        <span id="modalTradeDate"></span>
      </div>
    </div>
    <!-- Note -->
    <div class="text-[10px] text-[#aaa] mt-3">
      This transaction is processed via Benefit Market Trade. For any inquiries, please contact support.
    </div>
    <!-- Buttons -->
    <div class="mt-4 flex justify-between">
      <button id="downloadPdf" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 rounded text-xs">Download PDF</button>
      <button id="shareReceipt" class="px-3 py-1 bg-green-500 hover:bg-green-600 rounded text-xs">Share Receipt</button>
    </div>
  </div>
</div>

<!-- Include jsPDF & jsPDF-AutoTable Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
  document.getElementById("downloadPdf").addEventListener("click", function() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ unit: "pt", format: "a4" });
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();

  // --- Dark Header Section ---
  // Draw dark header rectangle (mimicking modal header)
  doc.setFillColor(26, 26, 26); // #1a1a1a
  doc.rect(40, 20, pageWidth - 80, 30, "F");

  // Header texts in white
  doc.setFont("Helvetica", "bold");
  doc.setFontSize(16);
  doc.setTextColor(255, 255, 255);
  doc.text("Benefit Market Trade", 50, 40);
  doc.text("TRADE INVOICE", pageWidth - 180 - 40, 40);

  // Draw horizontal divider below header
  doc.setLineWidth(1);
  doc.setDrawColor(100, 100, 100);
  doc.line(40, 55, pageWidth - 40, 55);

  // --- Trade Information Table ---
  doc.setFont("Helvetica", "normal");
  doc.setFontSize(10);
  doc.autoTable({
    startY: 65,
    theme: "plain",
    headStyles: { fontStyle: "bold", fontSize: 10, textColor: 150 },
    body: [
      ["Trade ID", document.getElementById("modalTradeId").innerText],
      ["Trade Date", document.getElementById("modalTradeDate").innerText],
      ["Category / Type", document.getElementById("modalTradeCatType").innerText]
    ]
  });

  // --- Trade Details Table ---
  const lineItemsHead = [["Description", "Lot Size", "Entry Price", "Amount"]];
  const lineItemsBody = [[
      document.getElementById("modalStatusDisplay").innerText,
      document.getElementById("modalLotSize").innerText,
      document.getElementById("modalEntryPrice").innerText,
      document.getElementById("modalAmountDisplay").innerText
  ]];

  doc.autoTable({
    startY: doc.lastAutoTable.finalY + 10,
    head: lineItemsHead,
    body: lineItemsBody,
    styles: { fontSize: 10, halign: "left" }
  });

  // --- Totals (Placeholder) ---
  doc.autoTable({
    startY: doc.lastAutoTable.finalY + 10,
    theme: "plain",
    body: [
      ["Total Amount", document.getElementById("modalAmountDisplay").innerText]
    ],
    columnStyles: {
      0: { halign: "right", fontStyle: "bold", fontSize: 10 },
      1: { halign: "left", fontSize: 10 }
    }
  });

  // --- Footer Section ---
  doc.setFont("Helvetica", "italic");
  doc.setFontSize(8);
  doc.setTextColor(0, 0, 0);  // Black text for footer
  doc.text("Thank you for trading with Benefit Market Trade!", pageWidth / 2, pageHeight - 20, { align: "center" });

  // Save the PDF with a dynamic filename using the trade ID
  doc.save("trade_invoice_" + document.getElementById("modalTradeId").innerText + ".pdf");
});

</script>
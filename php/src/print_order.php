<?php
session_start();
include 'connect.php';

if (!isset($_GET['id'])) {
    die("Invalid Order ID");
}

$order_id = $_GET['id'];
$order_number = $_GET['order_number'] ?? $order_id; 

// Fetch Order Details
$sql = "SELECT pc.*, a.agent_name as supplier_name, c.category_name, u.username as requester_name
        FROM purchase_credit pc 
        JOIN agent a ON pc.agent_id = a.agent_id
        JOIN category c ON pc.category_id = c.category_id
        LEFT JOIN user u ON pc.user_id = u.user_id
        WHERE pc.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found");
}

// Format Data
$formatted_date = date('d/m/Y', strtotime($order['order_date']));
$po_number = !empty($order['order_number']) ? $order['order_number'] : str_pad($order['order_id'], 5, '0', STR_PAD_LEFT);
$supplier = $order['supplier_name'];
$unit_price = 1.00; 
$amount = $order['order_quantity'] * $unit_price;
$vat = $amount * 0.07;
$total_amount = $amount + $vat;

// Baht Text Function
function BahtText($num){
    if($num==0) return "ศูนย์บาทถ้วน";
    $num = str_replace(",","",$num);
    $num_decimal = explode(".",$num);
    $num = $num_decimal[0];
    $returnNumWord = "";   
    $lenNumber = strlen($num);
    $lenNumber2 = $lenNumber-1;
    $kaGroup = array("","สิบ","ร้อย","พัน","หมื่น","แสน","ล้าน","สิบ","ร้อย","พัน","หมื่น","แสน","ล้าน");
    $kaDigit = array("","หนึ่ง","สอง","สาม","สี่","ห้า","หก","เจ็ด","แปด","เก้า");
    $kaDigitDecimal = array("ศูนย์","หนึ่ง","สอง","สาม","สี่","ห้า","หก","เจ็ด","แปด","เก้า");
    $ii = 0;
    for($i=$lenNumber2;$i>=0;$i--){
        $kaNumWord[$i] = substr($num,$ii,1);
        $ii++;
    }
    $ii = 0;
    for($i=$lenNumber2;$i>=0;$i--){
        if(($kaNumWord[$i]==2 && $i==1) || ($kaNumWord[$i]==2 && $i==7)){
            $returnNumWord.= "ยี่";
        }elseif(($kaNumWord[$i]==2 && $i==0) || ($kaNumWord[$i]==2 && $i==6)){
            $returnNumWord.= "สอง";
        }elseif(($kaNumWord[$i]==1 && $i==0) || ($kaNumWord[$i]==1 && $i==6)){
            if($lenNumber==1){
                $returnNumWord.= "หนึ่ง";
            }else{
                $returnNumWord.= "เอ็ด";
            }
        }elseif($kaNumWord[$i]==1 && $i==1){
            $returnNumWord.= "";
        }elseif($kaNumWord[$i]==1 && $i==7){
            $returnNumWord.= "";
        }elseif($kaNumWord[$i]==0){
            if($i==6){
                $returnNumWord.= "ล้าน";
            }
        }else{
            $returnNumWord.= $kaDigit[$kaNumWord[$i]];
        }
        if($kaNumWord[$i]!=0){
            $returnNumWord.= $kaGroup[$i];
        }
        $ii++;
    }
    $returnNumWord.= "บาท";
    if(count($num_decimal)>1){
        $decimal = $num_decimal[1];
        if(strlen($decimal)==1) $decimal.="0";
        if($decimal=="00"){
             $returnNumWord.= "ถ้วน";
        }else{
            $decimal_len = strlen($decimal) - 1;
            for($i=0;$i<=$decimal_len;$i++){
                 $d_val = substr($decimal,$i,1);
                 if($i==0 && $d_val==2){
                     $returnNumWord.= "ยี่";
                 }elseif($i==0 && $d_val==1){
                     $returnNumWord.= "";
                 }else{
                     $returnNumWord.= $kaDigitDecimal[$d_val];
                 }
                 if($i==0) $returnNumWord.= "สิบ";
            }
            $returnNumWord.= "สตางค์";
        }
    }else{
        $returnNumWord.= "ถ้วน";
    }
    return $returnNumWord;
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order <?php echo $po_number; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
            font-size: 14px;
            line-height: 1.4;
        }
        
        /* Reset Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 5px;
            vertical-align: top;
        }

        /* Layout Tables (No Borders) */
        .layout-table, .layout-table td {
            border: none !important;
        }

        /* Content Tables (Borders) */
        .content-table {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 0; /* Remove bottom margin to connect with footer */
        }
        .content-table th, .content-table td {
            border: 1px solid #000 !important;
            padding: 8px;
        }
        .content-table th {
            text-align: center;
            background-color: #f0f0f0 !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
        }

        .footer-summary-table {
            width: 100%;
            border: 1px solid #000;
            border-top: none; /* Connect to content table provided it's flush */
            border-collapse: collapse;
        }
        .footer-summary-table td {
            border: 1px solid #000 !important;
            padding: 5px;
        }


        /* Helpers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }

        /* Spacer */
        .table-body-spacer {
            height: 350px; /* Adjust as needed for A4 */
        }
        
        /* Print Specifics */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0mm;
            }
            body { 
                margin: 10mm; /* Narrower margin for better fit */
                -webkit-print-color-adjust: exact; 
            }
            .no-print { display: none; }
            
            /* Ensure borders print */
            .content-table, .content-table th, .content-table td {
                border: 1px solid #000 !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Header Section -->
    <div style="margin-bottom: 10px;">
        <div style="font-size: 13px;">บริษัท .............................................................. จำกัด</div>
        <div>ที่อยู่ : ....................................................................................................................................................</div>
        <div>โทร. .......................................................................................................................................................</div>
        <div style="border-bottom: 2px solid #000; margin-top: 5px; margin-bottom: 15px;"></div>
    </div>

    <div class="text-center font-bold underline" style="font-size: 18px; margin-bottom: 15px;">ใบสั่งซื้อ / Purchase Order</div>

    <!-- Info Section (Layout Table) -->
    <table class="layout-table" style="margin-bottom: 15px;">
        <tr>
            <!-- Left Column -->
            <td style="width: 60%;">
                <table class="layout-table">
                    <tr>
                        <td style="width: 80px;" class="font-bold">เรียน / Attn</td>
                        <td><?php echo $supplier; ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold">ที่อยู่ / Add</td>
                        <td>...................................................................</td>
                    </tr>
                    <tr>
                        <td class="font-bold">โทร / Tel</td>
                        <td>............................ Fax: ........................</td>
                    </tr>
                    <tr>
                        <td class="font-bold">ผู้ติดต่อ</td>
                        <td>...................................................................</td>
                    </tr>
                </table>
            </td>
            <!-- Right Column -->
            <td style="width: 40%;">
                 <table class="layout-table">
                    <tr>
                        <td style="width: 120px;" class="font-bold">วันที่ / Date :</td>
                        <td><?php echo $formatted_date; ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold">เลขที่ใบเสนอราคา :</td>
                        <td>Ref-<?php echo $po_number; ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold">วิธีการชำระเงิน :</td>
                         <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Items Table (Content Table) -->
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 50px;">ลำดับ</th>
                <th>รายการ</th>
                <th style="width: 80px;">จำนวน</th>
                <th style="width: 100px;">ราคาต่อหน่วย</th>
                <th style="width: 120px;">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="text-left">
                    <?php echo $order['supplier_name']; ?>
                </td>
                <td class="text-right"><?php echo number_format($order['order_quantity']); ?></td>
                <td class="text-right"><?php echo number_format($unit_price, 2); ?></td>
                <td class="text-right"><?php echo number_format($amount, 2); ?></td>
            </tr>
            <!-- Spacer Row -->
             <tr>
                <td class="table-body-spacer">&nbsp;</td>
                <td class="table-body-spacer">&nbsp;</td>
                <td class="table-body-spacer">&nbsp;</td>
                <td class="table-body-spacer">&nbsp;</td>
                <td class="table-body-spacer">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Summary + Baht Text (Unified Table) -->
    <table class="footer-summary-table">
        <tr>
             <!-- Left: Baht Text and Signatures Container -->
             <td style="width: 65%; vertical-align: top; padding: 0; border: 1px solid #000 !important;">
                
                <!-- Baht Text Row -->
                <div style="border-bottom: 1px solid #000; padding: 5px; background-color: #f9f9f9;">
                    <b>ตัวหนังสือ:</b> <?php echo BahtText($total_amount); ?>
                </div>

                <!-- Signatures -->
                <div style="padding: 10px; margin-top: 20px;">
                    <div>(...................................................................)</div>
                    <div style="margin-left: 10px;">พนักงานผู้รับใบสั่งซื้อ</div>
                </div>

             </td>

             <!-- Right: Totals -->
             <td style="width: 35%; padding: 0; vertical-align: top; border: 1px solid #000 !important;">
                <table class="layout-table" style="width: 100%;">
                    <tr>
                        <td class="text-right" style="border-bottom: 1px solid #000 !important; border-right: 1px solid #000 !important; width: 60%;">รวมราคาสินค้า</td>
                        <td class="text-right" style="border-bottom: 1px solid #000 !important;"><?php echo number_format($amount, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-right" style="border-bottom: 1px solid #000 !important; border-right: 1px solid #000 !important;">ภาษีมูลค่าเพิ่ม (7%)</td>
                        <td class="text-right" style="border-bottom: 1px solid #000 !important;"><?php echo number_format($vat, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="text-right" style="border-right: 1px solid #000 !important;">จำนวนเงินรวมทั้งสิ้น</td>
                        <td class="text-right font-bold"><?php echo number_format($total_amount, 2); ?></td>
                    </tr>
                </table>
             </td>
        </tr>
    </table>

    <!-- Final Signatures -->
    <br><br>
    <table class="layout-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div style="border-top: 1px dotted #000; width: 200px; margin: 0 auto 10px;"></div>
                <div>ผู้สั่งซื้อ วันที่ ...../...../..........</div>
                <div>(<?php echo $order['requester_name']; ?>)</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div style="border-top: 1px dotted #000; width: 200px; margin: 0 auto 10px;"></div>
                <div>ผู้อนุมัติ วันที่ ...../...../..........</div>
            </td>
        </tr>
    </table>

</body>
</html>

<?php

namespace App\Services;

use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\StockItem;
use App\Models\StockItemAttribute;
use App\Models\StockItemBarcode;
use App\Models\StockItemPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Str;

class StockItemService
{

    function getItemIndexLabel($batchNo, $index)
    {
        $label = '';
        while ($index >= 0) {
            $label = chr($index % 26 + 65) . $label;
            $index = intval($index / 26) - 1;
        }
        return $batchNo . $label;
    }

    function generatePriceTag($itemName, $itemIndex, $sellingPrice, $itemCode)
    {
        $generator = new BarcodeGeneratorPNG();
        $barcodeData = $generator->getBarcode($itemCode, $generator::TYPE_CODE_128, 2, 60);

        $barcodeImg = imagecreatefromstring($barcodeData);
        $barcodeWidth = imagesx($barcodeImg);
        $barcodeHeight = imagesy($barcodeImg);

        $finalWidth = max(300, $barcodeWidth + 20);
        $finalHeight = $barcodeHeight + 80;

        $finalImg = imagecreatetruecolor($finalWidth, $finalHeight);
        $white = imagecolorallocate($finalImg, 255, 255, 255);
        $black = imagecolorallocate($finalImg, 0, 0, 0);
        imagefill($finalImg, 0, 0, $white);

        // Item name
        imagestring($finalImg, 5, 10, 5, $itemName, $black);

        // Index + price top-right
        $priceText = "Index: $itemIndex  Price: ₹. $sellingPrice /-";
        imagestring($finalImg, 4, $finalWidth - 10 - imagefontwidth(4) * strlen($priceText), 25, $priceText, $black);

        // Barcode image center
        imagecopy($finalImg, $barcodeImg, intval(($finalWidth - $barcodeWidth) / 2), 40, 0, 0, $barcodeWidth, $barcodeHeight);

        // Item code bottom
        imagestring($finalImg, 4, 10, $finalHeight - 20, "CODE: $itemCode", $black);

        ob_start();
        imagepng($finalImg);
        $finalPng = ob_get_clean();

        $fileName = 'barcodes/' . $itemName . '_' . $itemIndex . '.png';
        Storage::put($fileName, $finalPng);

        imagedestroy($barcodeImg);
        imagedestroy($finalImg);

        return $fileName;
    }

    // public function store($request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $purchaseEntryId = $request->purchase_entry_id;
    //         $purchaseEntryItemId = $request->purchase_entry_item_id;
    //         $itemId = $request->item_id;
    //         $itemName = $request->item_name;
    //         $categoryId = $request->category_id;
    //         $categoryName = $request->category_name;

    //         $batchNo = 1;

    //         foreach ($request->item_lists as $itemList) {
    //             $sellingPrice = $itemList['selling_price'];
    //             $quantity = $itemList['quantity'];
    //             $attributes = $itemList['attributes'];

    //             for ($i = 0; $i < $quantity; $i++) {
    //                 $itemIndex = $this->getItemIndexLabel($batchNo, $i);
    //                 $itemCode = strtoupper(Str::random(8));

    //                 $stockItem = StockItem::create([
    //                     'purchase_entry_id' => $purchaseEntryId,
    //                     'purchase_entry_item_id' => $purchaseEntryItemId,
    //                     'item_id' => $itemId,
    //                     'item_name' => $itemName,
    //                     'category_id' => $categoryId,
    //                     'category_name' => $categoryName,
    //                     'item_code' => $itemCode,
    //                     'status' => 'available'
    //                 ]);

    //                 // $purchaseEntryItem = PurchaseEntryItem::find($purchaseEntryItemId);

    //                 foreach ($attributes as $attr) {
    //                     StockItemAttribute::create([
    //                         'stock_item_id' => $stockItem->id,
    //                         'attribute_id' => $attr['attribute_id'],
    //                         'attribute_name' => $attr['attribute_name'],
    //                         'attribute_value_id' => $attr['attribute_value_id'],
    //                         'attribute_value_name' => $attr['attribute_value_name']
    //                     ]);
    //                 }

    //                 // Generate price tag barcode image
    //                 $barcodePath = $this->generatePriceTag($itemName, $itemIndex, $sellingPrice, $itemCode);

    //                 StockItemBarcode::create([
    //                     'stock_item_id' => $stockItem->id,
    //                     'barcode_value' => $itemCode,
    //                     'barcode_image' => $barcodePath,
    //                     'item_index' => $itemIndex,
    //                     'status' => true
    //                 ]);

    //                 StockItemPrice::create([
    //                     'stock_item_id' => $stockItem->id,
    //                     'selling_price' => $sellingPrice,
    //                     'status' => true
    //                 ]);
    //             }

    //             $batchNo++;
    //         }

    //         DB::commit();
    //         return response()->json(['message' => 'Stock items created successfully'], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
    //     }
    // }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $purchaseEntryId = $request->purchase_entry_id;
            $batchNo = 1;
            $statusFlag = true;

            $purchaseEntry = PurchaseEntry::find($purchaseEntryId);

            foreach ($request->items as $item) {
                $itemId = $item['item_id'];
                $itemName = $item['item_name'];
                $categoryId = $item['category_id'];
                $categoryName = $item['category_name'];
                $purchaseEntryItemId = $item['purchase_entry_item_id'];


                foreach ($item['item_lists'] as $itemList) {
                    $sellingPrice = $itemList['selling_price'];
                    $quantity = $itemList['quantity'];
                    $attributes = $itemList['attributes'];

                    for ($i = 0; $i < $quantity; $i++) {
                        $itemIndex = $this->getItemIndexLabel($batchNo, $i);
                        $itemCode = strtoupper(Str::random(8));

                        $stockItem = StockItem::create([
                            'purchase_entry_id' => $purchaseEntryId,
                            'purchase_entry_item_id' => $purchaseEntryItemId,
                            'item_id' => $itemId,
                            'item_name' => $itemName,
                            'category_id' => $categoryId,
                            'category_name' => $categoryName,
                            'item_code' => $itemCode,
                            'status' => 'available'
                        ]);

                        foreach ($attributes as $attr) {
                            StockItemAttribute::create([
                                'stock_item_id' => $stockItem->id,
                                'attribute_id' => $attr['attribute_id'],
                                'attribute_name' => $attr['attribute_name'],
                                'attribute_value_id' => $attr['attribute_value_id'],
                                'attribute_value_name' => $attr['attribute_value_name']
                            ]);
                        }

                        $barcodePath = $this->generatePriceTag($itemName, $itemIndex, $sellingPrice, $itemCode);

                        StockItemBarcode::create([
                            'stock_item_id' => $stockItem->id,
                            'barcode_value' => $itemCode,
                            'barcode_image' => $barcodePath,
                            'item_index' => $itemIndex,
                            'status' => true
                        ]);

                        StockItemPrice::create([
                            'stock_item_id' => $stockItem->id,
                            'selling_price' => $sellingPrice,
                            'status' => true
                        ]);
                    }

                    $purchaseEntryItem = PurchaseEntryItem::find($purchaseEntryItemId);
                    $purchaseEntryItem->barcoded_quantity += $quantity;
                    $purchaseEntryItem->pending_quantity -= $quantity;
                    $purchaseEntryItem->status = $purchaseEntryItem->pending_quantity == 0 ? true : false;
                    $purchaseEntryItem->save();



                    $batchNo++;
                }
                if (!$purchaseEntryItem->status) {
                    $statusFlag = false;
                }
            }

            $purchaseEntry->status = $statusFlag ? 'Barcoded' : 'Partially Barcoded';

            $purchaseEntry->save();

            DB::commit();
            return response()->json(['message' => 'Stock items created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }




    public function getByBarcode($barcode)
    {
        $barcode = StockItemBarcode::where('barcode_value', $barcode)->first();

        if (!$barcode) {
            return response()->json(['message' => 'Barcode not found'], 404);
        }

        $item = $barcode->stockItem()->with([
            'attributes:stock_item_id,attribute_name,attribute_value_name',
            'prices' => function ($q) {
                $q->where('status', true);
            }
        ])->first();

        return response()->json([
            'barcode_value' => $barcode->barcode_value,
            'item_index' => $barcode->item_index,
            'item_name' => $item->item_name,
            'category_name' => $item->category_name,
            'selling_price' => $item->prices->first()?->selling_price,
            'attributes' => $item->attributes->map(function ($attr) {
                return [
                    'attribute_name' => $attr->attribute_name,
                    'attribute_value_name' => $attr->attribute_value_name
                ];
            })
        ]);
    }
}

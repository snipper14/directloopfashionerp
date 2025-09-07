<?php

namespace App\Http\Controllers\PrintLabels;

use Exception;
use Mike42\Escpos\Printer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrintLabelController extends Controller
{
    function printLabels()
    {
        try {
            $connector = new WindowsPrintConnector("smb://192.168.100.41/LLBPRINTER");
            $printer = new Printer($connector);
            $fonts = [
                Printer::FONT_A,
                Printer::FONT_B,
                Printer::FONT_C
            ];
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setFont($fonts[1]);
            $printer->setTextSize(2, 2);
            $printer->text("Test Label\n");
            $printer->cut();
            $printer->close();

            echo "Test label printed!";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    public function printLabels1(Request $request)
    {
        // Retrieve printer name from configuration or database

        try {
            // Connect to the printer
            $connector = new WindowsPrintConnector("LLBPRINTER");
            $printer = new Printer($connector);

            // Labels Data
            $labels = $request->labels; // Example: array of labels with text and barcode values

            foreach ($labels as $label) {
                echo $label['barcode'];
                // Set label size for 2x1
                $printer->setJustification(Printer::JUSTIFY_CENTER);

                // Print Barcode
                $printer->setBarcodeHeight(60); // Adjust for 2x1 label size
                $printer->barcode($label['barcode'], Printer::BARCODE_CODE39);

                // Print Text Below Barcode
                $printer->text("\n");
                $printer->setTextSize(1, 1); // Small text size
                $printer->text($label['text'] . "\n");

                // Space between labels
                $printer->feed(2);
            }

            // Cut the paper
            $printer->cut();

            // Close the connection
            $printer->close();

            return response()->json(['message' => 'Labels printed successfully!'], 200);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

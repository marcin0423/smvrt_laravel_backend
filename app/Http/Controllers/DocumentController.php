<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentType;

use Aspose\Words\WordsApi;
use Aspose\Words\Model\Requests\ConvertDocumentRequest;

class DocumentController extends Controller
{
    public function get_types(Request $request) {
        $types = DocumentType::all();
        return response()->json([
            'data' => $types,
        ]);
    }

    public function search_property(Request $request) {
        $type = $request->input('type');
        $search = $request->input('search') ?? '';

        $data = [
            'type' => [
                'NDA',
                'MSA',
                'SOW'
            ],
            'category' => [
                'My Documents',
                'Business',
                'Music',
                'Sales',
            ]
        ];

        $items = [];

        if ($search) {
            $items = collect($data[$type])->filter(function($item) use ($search) {
                $search = strtolower($search);
                return strpos(strtolower($item), $search) !== false;
            })->values()->all();
        }

        return response()->json([
            'data' => $items
        ]);
    }

    public function document_convert(Request $request) {
        $file = $request->file('file');
        $document = $file->store('public/documents');
        $requestDocument = storage_path('app/' . $document);
        if (in_array($file->getClientOriginalExtension(), ['doc', 'docx'])) {
            // $path = $file->getRealPath();
            // $content = \PhpOffice\PhpWord\IOFactory::load($path);
            // $writer = \PhpOffice\PhpWord\IOFactory::createWriter($content, 'HTML');

            $wordsApi = new WordsApi('e9067767-1fbb-404b-8296-d313d93cfd8a', 'c5796e6086ef983a32da13d93199eed9');

            $request = new ConvertDocumentRequest(
                $requestDocument, "html", NULL, NULL, NULL, NULL
            );
            $content = $wordsApi->convertDocument($request);
            $htmlContent = $content->getPathName();
            $htmlContent = file_get_contents($htmlContent);
            
            
            // ob_start();
            // $writer->save("php://output");
            // $content = ob_get_contents();
            // ob_end_clean();

            return response()->json([
                'data' => $htmlContent
            ]);
        }

        return response()->json([
            'data' => file_get_contents($file->getRealPath()),
        ]);
    }
}

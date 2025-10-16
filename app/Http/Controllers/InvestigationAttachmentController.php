<?php

namespace App\Http\Controllers;

use App\Models\InvestigationAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvestigationAttachmentController extends Controller
{
    /**
     * Download an investigation attachment.
     */
    public function download($attachmentId): StreamedResponse
    {
        // Check permission
        if (!Auth::user()->hasPermission('view_client_reports')) {
            abort(403, 'You do not have permission to download attachments.');
        }

        $attachment = InvestigationAttachment::findOrFail($attachmentId);

        // Check if file exists
        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->original_filename
        );
    }
}
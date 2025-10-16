<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signature Acknowledgment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full bg-white rounded-lg shadow-xl p-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Signature Acknowledgment</h2>
            
            <div class="mb-6 text-gray-600 leading-relaxed">
                I acknowledge receipt of the client's electronic signature for this specific contract only. I understand and affirm that this e-signature is exclusively authorized for the present document and cannot be used, reproduced, or applied to any other contracts or documents. Any misuse of this electronic signature is strictly prohibited, constitutes a violation of professional ethics, and is subject to legal penalties under applicable electronic signature laws.
            </div>

            <div class="flex items-center mb-6">
                <input type="checkbox" id="acknowledgment" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2">
                <label for="acknowledgment" class="text-sm text-gray-700">I have read and accept these terms</label>
            </div>

            <div class="flex justify-end space-x-4">
                <button onclick="window.close()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button onclick="viewSignature()" id="viewButton" disabled class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    View Signature
                </button>
            </div>
        </div>
    </div>

    <script>
        const checkbox = document.getElementById('acknowledgment');
        const viewButton = document.getElementById('viewButton');
        const signatureUrl = '{{ $signatureUrl }}';

        checkbox.addEventListener('change', function() {
            viewButton.disabled = !this.checked;
        });

        function viewSignature() {
            if (checkbox.checked) {
                window.location.href = signatureUrl;
            }
        }
    </script>
</body>
</html> 
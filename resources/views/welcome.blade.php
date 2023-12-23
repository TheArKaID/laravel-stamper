<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <style>
        #canvas-container {
            position: relative;
            width: max-content;
            height: max-content;
            padding: 0px;
        }
        .draggable {
            display: none;
            width: 87px;
            height: 87px;
            background-color: #9465ab;
            touch-action: none;
            user-select: none;
            text-align: center;
            padding: 30px 0;
            color: white;
            position: absolute;
        }
    </style>
    <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            <div class="max-w-7xl mx-auto p-6 lg:p-8">
                <div class="flex justify-center dark:text-white">
                    <form action="" method="post" enctype="multipart/form-data">
                        @csrf
                        <!-- component -->
                        <div class="flex w-full items-center justify-center bg-grey-lighter">
                            <label
                                class="w-64 flex flex-col items-center px-4 py-6 bg-white dark:bg-grey-lighter text-blue rounded-lg shadow-lg tracking-wide uppercase border border-blue cursor-pointer hover:bg-blue hover:text-white">
                                <svg class="w-8 h-8" fill="black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path d="M16.88 9.1A4 4 0 0 1 16 17H5a5 5 0 0 1-1-9.9V7a3 3 0 0 1 4.52-2.59A4.98 4.98 0 0 1 17 8c0 .38-.04.74-.12 1.1zM11 11h3l-4-4-4 4h3v3h2v-3z" />
                                </svg>
                                <span class="mt-2 text-base dark:text-black leading-normal">Select a file</span>
                                <input type='file' name="pdf-file" class="hidden" id="document-result" accept=".pdf" required/>
                                <hr>
                            </label>
                        </div>
                        <div class="flex w-full bg-grey-lighter" id="canvas-container">
                            <div class="draggable"> QR </div>
                            <canvas class="border-solid border-2 dark:border-zinc-50" id="pdf-canvas"> ~ PDF ~</canvas>
                        </div>
                        <input type="hidden" id="stampX" name="stampX">
                        <input type="hidden" id="stampY" name="stampY">
                        <input type="hidden" id="canvasHeight" name="canvasHeight">
                        <input type="hidden" id="canvasWidth" name="canvasWidth">
                        {{-- btn --}}
                        <div class="flex w-full items-center justify-center bg-black-#334155">
                            <button type="submit" class="w-32 mt-3 flex flex-col items-center bg-black-#334155 dark:bg-black-#334155 text-blue rounded-lg shadow-lg tracking-wide uppercase border border-blue cursor-pointer hover:bg-blue hover:text-white">
                                <span class="mt-2 text-base dark:text-white leading-normal">Submit</span>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="bottom-0 left-0 right-0 z-40 px-4 py-3 text-center text-white bg-gray-800">
                    <a href="https://github.com/sponsors/taylorotwell" class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="-mt-px mr-1 w-5 h-5 stroke-gray-400 dark:stroke-gray-600 group-hover:stroke-gray-600 dark:group-hover:stroke-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        Sponsor
                    </a>
                    &nbsp;
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                </div>
            </div>
        </div>
    </body>

    <script type="module" src='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.0.269/pdf.min.mjs'></script>
    <script type="module" src='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.0.269/pdf.worker.min.mjs'></script>

    <script>
        document.querySelector("#document-result").addEventListener("change", async function(e){
            var file = e.target.files[0]
            if(file.type != "application/pdf"){
                alert(file.name, "is not a pdf file.")
                return
            }

            var fileReader = new FileReader();  

            fileReader.onload = async function() {
                var typedarray = new Uint8Array(this.result);

                const loadingTask = pdfjsLib.getDocument(typedarray);
                loadingTask.promise.then(pdf => {
                    // you can now use *pdf* here
                    pdf.getPage(pdf.numPages).then(function(page) {
                        // you can now use *page* here
                        var scale = 1.5;
                        var viewport = page.getViewport({scale: scale});
                        var canvas = document.getElementById('pdf-canvas');
                        // Remove class border-2
                        canvas.classList.remove('border-2');

                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        document.getElementById('canvasHeight').value = viewport.height;
                        document.getElementById('canvasWidth').value = viewport.width;

                        page.render({
                            canvasContext: canvas.getContext('2d'),
                            viewport: viewport
                        });
                    });

                });
            };

            fileReader.readAsArrayBuffer(file);

            document.getElementsByClassName('draggable')[0].style.display = 'block';
        })
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.20/dist/interact.min.js"></script>

    <script>
        const position = { x: 0, y: 0 }
        interact('.draggable').draggable({
            listeners: {
                move (event) {
                    position.x += event.dx
                    position.y += event.dy

                    event.target.style.transform =
                        `translate(${position.x}px, ${position.y}px)`
                },
                end (event) {
                    var style = window.getComputedStyle(event.target);
                    var matrix = new WebKitCSSMatrix(style.transform);

                    console.log(matrix.m41, matrix.m42)
                    document.getElementById('stampX').value = matrix.m41;
                    document.getElementById('stampY').value = matrix.m42;
                }
            },
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: 'parent',
                    endOnly: true
                })
            ],
        })
    </script>

</html>

<footer class="bg-[#212121] px-6 py-12 text-white font-sans" id="footer">
@php
    $currentLang = session('app_language', 'fr');
    $langPrefix = $currentLang === 'en' ? '/en' : '';
@endphp
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-10">
            <h2 class="text-xl font-bold tracking-wide" data-i18n="footer_locate">Locate Us</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            
            <div class="flex flex-col items-center md:items-start">
                <img src="{{ asset('images/HP-logo-290x91.png') }}" alt="Hello Passenger" class="h-40 w-auto mb-8">
                
                <div class="space-y-4 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="text-yellow-500 text-lg">📞</span>
                        <p>+33 (0)1 34 38 58 98</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-yellow-500 text-lg">✉️</span>
                        <p><a href="mailto:contact@hellopassenger.com" class="hover:underline">contact@hellopassenger.com</a></p>
                    </div>
                </div>

                <div class="flex gap-4 mt-6">
                    <a href="https://www.facebook.com/hello.passenger.officiel/" target="_blank" rel="noopener noreferrer" class="w-8 h-8 bg-blue-600 flex items-center justify-center rounded-full hover:opacity-80 transition-opacity">
                        <span class="text-white text-xs">f</span>
                    </a>
                    <a href="https://www.instagram.com/hellopassenger_officiel/" target="_blank" rel="noopener noreferrer" class="w-8 h-8 bg-gradient-to-tr from-yellow-500 to-purple-500 flex items-center justify-center rounded-sm hover:opacity-80 transition-opacity">
                        <span class="text-white text-xs font-bold">IG</span>
                    </a>
                </div>
            </div>

            <div class="text-center md:text-left">
                <h3 class="text-yellow-500 font-bold mb-4 uppercase text-sm">Paris CDG Airport</h3>
                <ul class="text-sm space-y-3 text-gray-200">
                    <li>Terminal 2</li>
                    <li>TGV Railway station – Level 4</li>
                    <li>Opposition Sheraton Hotel, between</li>
                    <li>Terminal 2C et 2E</li>
                </ul>
            </div>

            <div class="text-center md:text-left">
                <h3 class="text-yellow-500 font-bold mb-4 uppercase text-sm">Paris ORLY Airport</h3>
                <ul class="text-sm space-y-3 text-gray-200">
                    <li>Terminal 3</li>
                    <li>Arrival level</li>
                </ul>
            </div>

            <div class="text-center md:text-left">
                <h3 class="text-white font-bold mb-4 text-sm" data-i18n="footer_quick_links">Quick Links</h3>
                <ul class="text-sm space-y-3 text-gray-200">
                    <li><a href="#" class="hover:text-yellow-500 transition-colors" data-i18n="footer_transport">Transport of luggage</a></li>
                    <li><a href="#" class="hover:text-yellow-500 transition-colors" data-i18n="footer_left_luggage">Left luggage</a></li>
                    <li><a href="#" class="hover:text-yellow-500 transition-colors" data-i18n="footer_lost_object">Recover my lost object</a></li>
                </ul>
            </div>
        </div>

        <div class="max-w-3xl mx-auto text-center border-t border-gray-700 pt-8 mb-8">
            <p class="text-gray-300 text-sm leading-relaxed" data-i18n="footer_description">
                Hello Passenger is a platform that allows you to book a transport of luggage to or from the airport as well as to store your luggage in consignment in our agency located in Paris CDG and Paris ORLY.
            </p>
        </div>

        <div class="text-center text-sm space-y-3">
            <a href="{{ route('form-consigne') }}" class="text-white hover:text-yellow-500 transition-colors font-semibold">
                Home
            </a>
            <p class="text-white">
                © 2026 All Rights Reserved. Created by <span class="text-yellow-500 font-bold">Blablabla Agency</span>
            </p>
        </div>
    </div>
</footer>
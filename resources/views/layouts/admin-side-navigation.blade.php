@php
    $menuItems = [
        [
            'route' => 'admin.dashboard',
            'title' => 'Dashboard',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'dashboard', 'title' => 'Dashboard'],
            ],
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>'
        ],
        // [
        //     'route' => 'admin.plans.index',
        //     'title' => 'Plans',
        //     'svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
        //                                             stroke="currentColor" class="size-6">
        //                                             <path stroke-linecap="round" stroke-linejoin="round"
        //                                                 d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
        //                                         </svg>'
        // ],
        // [
        //     'route' => 'admin.units.index',
        //     'title' => 'Units',
        //     'svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
        //                                             stroke="currentColor" class="size-6">
        //                                             <path stroke-linecap="round" stroke-linejoin="round"
        //                                                 d="M7.58209 8.96025 9.8136 11.1917l-1.61782 1.6178c-1.08305-.1811-2.23623.1454-3.07364.9828-1.1208 1.1208-1.32697 2.8069-.62368 4.1363.14842.2806.42122.474.73509.5213.06726.0101.1347.0133.20136.0098-.00351.0666-.00036.1341.00977.2013.04724.3139.24069.5867.52125.7351 1.32944.7033 3.01552.4971 4.13627-.6237.8375-.8374 1.1639-1.9906.9829-3.0736l4.8107-4.8108c1.0831.1811 2.2363-.1454 3.0737-.9828 1.1208-1.1208 1.3269-2.80688.6237-4.13632-.1485-.28056-.4213-.474-.7351-.52125-.0673-.01012-.1347-.01327-.2014-.00977.0035-.06666.0004-.13409-.0098-.20136-.0472-.31386-.2406-.58666-.5212-.73508-1.3294-.70329-3.0155-.49713-4.1363.62367-.8374.83741-1.1639 1.9906-.9828 3.07365l-1.7788 1.77875-2.23152-2.23148-1.41419 1.41424Zm1.31056-3.1394c-.04235-.32684-.24303-.61183-.53647-.76186l-1.98183-1.0133c-.38619-.19746-.85564-.12345-1.16234.18326l-.86321.8632c-.3067.3067-.38072.77616-.18326 1.16235l1.0133 1.98182c.15004.29345.43503.49412.76187.53647l1.1127.14418c.3076.03985.61628-.06528.8356-.28461l.86321-.8632c.21932-.21932.32446-.52801.2846-.83561l-.14417-1.1127ZM19.4448 16.4052l-3.1186-3.1187c-.7811-.781-2.0474-.781-2.8285 0l-.1719.172c-.7811.781-.7811 2.0474 0 2.8284l3.1186 3.1187c.7811.781 2.0474.781 2.8285 0l.1719-.172c.7811-.781.7811-2.0474 0-2.8284Z" />
        //                                         </svg>',
        // ],
        //[
        //    'route' => 'admin.products.index',
        //    'title' => 'Products',
        //    'svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
        //                                            stroke="currentColor" class="size-6">
        //                                            <path stroke-linecap="round" stroke-line-join="round" stroke-width="0.8"
        //                                                d="M7 24h-5v-9h5v1.735c.638-.198 1.322-.495 1.765-.689.642-.28 1.259-.417 1.887-.417 1.214 0 2.205.499 4.303 1.205.64.214 1.076.716 1.175 1.306 1.124-.863 2.92-2.257 2.937-2.27.357-.284.773-.434 1.2-.434.952 0 1.751.763 1.751 1.708 0 .49-.219.977-.627 1.356-1.378 1.28-2.445 2.233-3.387 3.074-.56.501-1.066.952-1.548 1.393-.749.687-1.518 1.006-2.421 1.006-.405 0-.832-.065-1.308-.2-2.773-.783-4.484-1.036-5.727-1.105v1.332zm-1-8h-3v7h3v-7zm1 5.664c2.092.118 4.405.696 5.999 1.147.817.231 1.761.354 2.782-.581 1.279-1.172 2.722-2.413 4.929-4.463.824-.765-.178-1.783-1.022-1.113 0 0-2.961 2.299-3.689 2.843-.379.285-.695.519-1.148.519-.107 0-.223-.013-.349-.042-.655-.151-1.883-.425-2.755-.701-.575-.183-.371-.993.268-.858.447.093 1.594.35 2.201.52 1.017.281 1.276-.867.422-1.152-.562-.19-.537-.198-1.889-.665-1.301-.451-2.214-.753-3.585-.156-.639.278-1.432.616-2.164.814v3.888zm3.79-19.913l3.21-1.751 7 3.86v7.677l-7 3.735-7-3.735v-7.719l3.784-2.064.002-.005.004.002zm2.71 6.015l-5.5-2.864v6.035l5.5 2.935v-6.106zm1 .001v6.105l5.5-2.935v-6l-5.5 2.83zm1.77-2.035l-5.47-2.848-2.202 1.202 5.404 2.813 2.268-1.167zm-4.412-3.425l5.501 2.864 2.042-1.051-5.404-2.979-2.139 1.166z" />
        //                                        </svg>'
        //],
        // [
        //     'route' => 'admin.supplier.index',
        //     'title' => 'Suppliers',
        //     'svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="size-6"><path stroke="currentColor" stroke-linecap="round" stroke-line-join="round" stroke-width="1" d="M13 7h6l2 4m-8-4v8m0-8V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v9h2m8 0H9m4 0h2m4 0h2v-4m0 0h-5m3.5 5.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Zm-10 0a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z" /></svg>'
        // ],
        [
            'route' => 'admin.organization.index',
            'title' => 'Practices',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'admin.organization.index', 'title' => 'Practices'],
            ],
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>'
        ],


        [
            'route' => 'admin.purchase.index',
            'title' => 'Purchase Orders',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'admin.purchase.index', 'title' => 'Purchase Orders'],
            ],
            'svg' => '<svg class="shrink-0 size-6" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0,0,256,256"><g fill="currentColor" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"> <g transform="scale(2,2)"> <path d="M15,109.8l48,17v0c0.1,0 0.2,0.1 0.3,0.1c0.2,0.1 0.5,0.1 0.7,0.1c0.2,0 0.3,0 0.5,0v0c0,0 0,0 0.1,0c0.1,0 0.3,-0.1 0.4,-0.1v0l48,-17c1.2,-0.4 2,-1.6 2,-2.8v-33.7l10,-3.5c0.8,-0.3 1.5,-1 1.8,-1.8c0.3,-0.8 0.2,-1.8 -0.3,-2.6l-12,-20l-0.1,-0.1c0,-0.1 -0.1,-0.1 -0.1,-0.2v0c0,-0.1 -0.1,-0.1 -0.1,-0.2c0,0 0,0 0,-0.1c-0.1,-0.1 -0.1,-0.1 -0.2,-0.2l-0.1,-0.1l-0.1,-0.1h-0.1l-0.1,-0.1c-0.1,-0.1 -0.2,-0.1 -0.3,-0.1c-0.1,0 -0.1,-0.1 -0.2,-0.1v0v0l-48,-17c0,0 0,0 -0.1,0h-0.1h-0.1c-0.1,0 -0.1,0 -0.2,0v0v0c-0.1,0 -0.1,0 -0.2,0c-0.1,0 -0.1,0 -0.2,0c-0.1,0 -0.2,0 -0.4,0c-0.1,0 -0.1,0 -0.2,0c-0.2,0 -0.4,0.1 -0.5,0.1l-48,17c-0.2,0.1 -0.3,0.1 -0.5,0.2l-0.1,0.1c-0.1,0.1 -0.2,0.1 -0.3,0.2l-0.1,0.1c-0.1,0.1 -0.2,0.1 -0.2,0.2l-0.1,0.1c-0.1,0.1 -0.1,0.2 -0.2,0.2c0,0 0,0.1 -0.1,0.1l-12,20c-0.7,1.1 -0.6,2.5 0.2,3.4c0.6,0.7 1.4,1.1 2.3,1.1c0.3,0 0.7,-0.1 1,-0.2l8,-2.8v40c0,1.3 0.8,2.4 2,2.8zM119.5,65.4l-42.2,15l-8.9,-14.8l42.2,-15zM67,34.2l36,12.8l-36,12.8zM67,74.8l6.4,10.7c0.6,1 1.6,1.5 2.6,1.5c0.3,0 0.7,-0.1 1,-0.2l32,-11.3v29.4l-42,14.9zM19,51.2l42,14.9v53.6l-42,-14.9z"></path></g></g> </svg>'
        ],
        // [
        //     'route' => 'admin.users.index',
        //     'title' => 'Users',
        //     'svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-6 h-6">
        //                                             <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5a4.5 4.5 0 10-9 0 4.5 4.5 0 009 0zM21 20.25v-1.5a4.5 4.5 0 00-4.5-4.5h-9a4.5 4.5 0 00-4.5 4.5v1.5" />
        //                                         </svg>'
        // ],

        [
            'route' => 'admin.inventory.index',
            'title' => 'Inventory',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'admin.inventory.index', 'title' => 'Inventory'],
            ],
            'svg' =>
                '<svg  fill="currentColor" height="23px" width="23px" version="1.2" baseProfile="tiny" id="inventory" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 256 230" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M61.2,106h37.4v31.2H61.2V106z M61.2,178.7h37.4v-31.2H61.2V178.7z M61.2,220.1h37.4v-31.2H61.2V220.1z M109.7,178.7H147 v-31.2h-37.4V178.7z M109.7,220.1H147v-31.2h-37.4V220.1z M158.2,188.9v31.2h37.4v-31.2H158.2z M255,67.2L128.3,7.6L1.7,67.4 l7.9,16.5l16.1-7.7v144h18.2V75.6h169v144.8h18.2v-144l16.1,7.5L255,67.2z"></path> </g></svg>',
        ],



        // [
        //     'route' => 'billing_shipping.index',
        //     'title' => 'Billing and Shipping',
        //     'svg' => '<svg viewBox="0 0 32 32" id="svg5" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs id="defs2"></defs> <g id="layer1" transform="translate(36,-148)"> <path d="m -28,150 c -2.749574,0 -5,2.25042 -5,5 v 22 a 1.0001,1.0001 0 0 0 1.832031,0.55469 L -30,175.80273 l 1.167969,1.75196 a 1.0001,1.0001 0 0 0 1.664062,0 L -26,175.80273 l 1.167969,1.75196 a 1.0001,1.0001 0 0 0 1.664062,0 L -22,175.80273 l 1.167969,1.75196 a 1.0001,1.0001 0 0 0 1.664062,0 L -18,175.80273 l 1.167969,1.75196 A 1.0001,1.0001 0 0 0 -15,177 v -12 h 7 a 1.0001,1.0001 0 0 0 1,-1 v -9 c 0,-2.74958 -2.2504259,-5 -5,-5 z m 0,2 h 12.007812 C -16.624083,152.83731 -17,153.87659 -17,155 v 1 8 9.69727 l -0.167969,-0.25196 a 1.0001,1.0001 0 0 0 -1.664062,0 L -20,175.19727 l -1.167969,-1.75196 a 1.0001,1.0001 0 0 0 -1.664062,0 L -24,175.19727 l -1.167969,-1.75196 a 1.0001,1.0001 0 0 0 -1.664062,0 L -28,175.19727 l -1.167969,-1.75196 a 1.0001,1.0001 0 0 0 -1.664062,0 L -31,173.69727 V 155 c 0,-1.6687 1.331303,-3 3,-3 z m 16,0 c 1.668697,0 3,1.3313 3,3 v 8 h -6 v -7 -1 c 0,-1.6687 1.331303,-3 3,-3 z" id="rect1587" style="color:currentColor;fill:currentColor;fill-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.1;-inkscape-stroke:none"></path> <path d="m -28,162 a 1,1 0 0 0 -1,1 1,1 0 0 0 1,1 h 4 a 1,1 0 0 0 1,-1 1,1 0 0 0 -1,-1 z" id="path6019" style="color:#000000;fill:currentColor;fill-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.1;-inkscape-stroke:none"></path> <path d="m -28,165 a 1,1 0 0 0 -1,1 1,1 0 0 0 1,1 h 8 a 1,1 0 0 0 1,-1 1,1 0 0 0 -1,-1 z" id="path6021" style="color:currentColor;fill:currentColor;fill-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.1;-inkscape-stroke:none"></path> <path d="m -28,168 a 1,1 0 0 0 -1,1 1,1 0 0 0 1,1 h 8 a 1,1 0 0 0 1,-1 1,1 0 0 0 -1,-1 z" id="path6023" style="color:currentColor;fill:currentColor;fill-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.1;-inkscape-stroke:none"></path> <path d="m -28,155 a 1.0001,1.0001 0 0 0 -1,1 v 4 a 1.0001,1.0001 0 0 0 1,1 h 4 a 1.0001,1.0001 0 0 0 1,-1 v -4 a 1.0001,1.0001 0 0 0 -1,-1 z m 1,2 h 2 v 2 h -2 z" id="rect6025" style="color:currentColor;fill:currentColor;fill-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.1;-inkscape-stroke:none"></path> </g> </g></svg>'
        // ],
        [
            'route' => 'report.index',
            'title' => 'Reports',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'report.index', 'title' => 'Reports'],
            ],
            'svg' => '<svg class="shrink-0 size-6" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 4h3a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3m0 3h6m-3 5h3m-6 0h.01M12 16h3m-6 0h.01M10 3v4h4V3h-4Z" /></svg>',
        ],
        [
            'route' => 'admin.blogs.index',
            'title' => 'Blogs',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'admin.blogs.index', 'title' => 'Blogs'],
            ],
            'svg' => '<svg viewBox="0 0 24 24" height="23px" width="23px" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M22 7.662l1-1V18h-7v4.745L11.255 18H1V2h16.763l-1 1H2v14h9.668L15 20.331V17h7zm1.657-5.192a.965.965 0 0 1 .03 1.385l-9.325 9.324-4.097 1.755a.371.371 0 0 1-.487-.487l1.755-4.097 9.31-9.309a.98.98 0 0 1 1.385 0zm-10.1 9.965l-1.28-1.28-.961 2.24zm7.243-7.11l-1.414-1.413-6.469 6.47 1.414 1.413zm1.865-2.445l-.804-.838a.42.42 0 0 0-.6-.006l-1.168 1.168 1.414 1.415 1.152-1.152a.42.42 0 0 0 .006-.587z"></path><path fill="none" d="M0 0h24v24H0z"></path></g></svg>',
        ],
        [
            'route' => 'potential-users.index',
            'title' => 'Potential users',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'potential-users.index', 'title' => 'Potential Users'],
            ],
            'svg' => '<svg fill="currentColor" height="23px" width="23px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>users</title> <path d="M16 21.416c-5.035 0.022-9.243 3.537-10.326 8.247l-0.014 0.072c-0.018 0.080-0.029 0.172-0.029 0.266 0 0.69 0.56 1.25 1.25 1.25 0.596 0 1.095-0.418 1.22-0.976l0.002-0.008c0.825-3.658 4.047-6.35 7.897-6.35s7.073 2.692 7.887 6.297l0.010 0.054c0.127 0.566 0.625 0.982 1.221 0.982 0.69 0 1.25-0.559 1.25-1.25 0-0.095-0.011-0.187-0.031-0.276l0.002 0.008c-1.098-4.78-5.305-8.295-10.337-8.316h-0.002zM9.164 11.102c0 0 0 0 0 0 2.858 0 5.176-2.317 5.176-5.176s-2.317-5.176-5.176-5.176c-2.858 0-5.176 2.317-5.176 5.176v0c0.004 2.857 2.319 5.172 5.175 5.176h0zM9.164 3.25c0 0 0 0 0 0 1.478 0 2.676 1.198 2.676 2.676s-1.198 2.676-2.676 2.676c-1.478 0-2.676-1.198-2.676-2.676v0c0.002-1.477 1.199-2.674 2.676-2.676h0zM22.926 11.102c2.858 0 5.176-2.317 5.176-5.176s-2.317-5.176-5.176-5.176c-2.858 0-5.176 2.317-5.176 5.176v0c0.004 2.857 2.319 5.172 5.175 5.176h0zM22.926 3.25c1.478 0 2.676 1.198 2.676 2.676s-1.198 2.676-2.676 2.676c-1.478 0-2.676-1.198-2.676-2.676v0c0.002-1.477 1.199-2.674 2.676-2.676h0zM31.311 19.734c-0.864-4.111-4.46-7.154-8.767-7.154-0.395 0-0.784 0.026-1.165 0.075l0.045-0.005c-0.93-2.116-3.007-3.568-5.424-3.568-2.414 0-4.49 1.448-5.407 3.524l-0.015 0.038c-0.266-0.034-0.58-0.057-0.898-0.063l-0.009-0c-4.33 0.019-7.948 3.041-8.881 7.090l-0.012 0.062c-0.018 0.080-0.029 0.173-0.029 0.268 0 0.691 0.56 1.251 1.251 1.251 0.596 0 1.094-0.417 1.22-0.975l0.002-0.008c0.684-2.981 3.309-5.174 6.448-5.186h0.001c0.144 0 0.282 0.020 0.423 0.029 0.056 3.218 2.679 5.805 5.905 5.805 3.224 0 5.845-2.584 5.905-5.794l0-0.006c0.171-0.013 0.339-0.035 0.514-0.035 3.14 0.012 5.765 2.204 6.442 5.14l0.009 0.045c0.126 0.567 0.625 0.984 1.221 0.984 0.69 0 1.249-0.559 1.249-1.249 0-0.094-0.010-0.186-0.030-0.274l0.002 0.008zM16 18.416c-0 0-0 0-0.001 0-1.887 0-3.417-1.53-3.417-3.417s1.53-3.417 3.417-3.417c1.887 0 3.417 1.53 3.417 3.417 0 0 0 0 0 0.001v-0c-0.003 1.886-1.53 3.413-3.416 3.416h-0z"></path> </g></svg>',
        ],


        [
            'route' => 'ticket.index',
            'title' => 'Tickets',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'ticket.index', 'title' => 'Tickets'],
            ],
            'svg' => '
                    <svg class="shrink-0 size-6" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="M7 9h10M7 13h5"/>
                </svg>'

        ],

        [
            'route' => 'admin.medrep.organization.index',
            'title' => 'Medrep Practices',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'admin.medrep.organization.index', 'title' => 'Medrep Practices'],
            ],
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4" />
                    <line x1="19" y1="8" x2="19" y2="14" />
                    <line x1="22" y1="11" x2="16" y2="11" /></svg>'
        ],

        [
            'route' => 'admin.settings.index',
            'title' => 'Settings',
            'hasSubmenu' => true,
            'submenu' => [
                ['route' => 'admin.settings.index', 'title' => 'Settings'],
            ],
            'svg' => '<svg class="shrink-0 size-6" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0,0,256,256"><g fill="currentColor" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M22.20508,2c-0.48953,0.00026 -0.90693,0.35484 -0.98633,0.83789l-0.97266,5.95508c-1.16958,0.34023 -2.28485,0.7993 -3.33594,1.37109l-4.91406,-3.50977c-0.39728,-0.28369 -0.94131,-0.23911 -1.28711,0.10547l-3.89062,3.88672c-0.3432,0.34344 -0.39015,0.88376 -0.11133,1.28125l3.45703,4.94531c-0.58061,1.05722 -1.04985,2.17878 -1.39844,3.35938l-5.92969,0.98633c-0.4815,0.0811 -0.83404,0.49805 -0.83398,0.98633v5.5c-0.00088,0.48518 0.3466,0.901 0.82422,0.98633l5.93359,1.05078c0.3467,1.17855 0.81296,2.30088 1.39453,3.35937l-3.5,4.89648c-0.28369,0.39728 -0.23911,0.94131 0.10547,1.28711l3.88867,3.89063c0.34265,0.34275 0.88175,0.39048 1.2793,0.11328l4.95508,-3.46875c1.05419,0.57517 2.17218,1.03762 3.3457,1.38086l0.99023,5.96289c0.08025,0.48228 0.49742,0.83584 0.98633,0.83594h5.5c0.4858,0.00071 0.90184,-0.34778 0.98633,-0.82617l1.06055,-5.98633c1.16868,-0.3485 2.28142,-0.8178 3.33008,-1.39648l4.98828,3.5c0.39749,0.27882 0.93781,0.23187 1.28125,-0.11133l3.88867,-3.89258c0.34612,-0.34687 0.38995,-0.89343 0.10352,-1.29102l-3.55664,-4.9375c0.56867,-1.04364 1.02681,-2.14972 1.36719,-3.31055l6.01758,-1.05469c0.47839,-0.08448 0.82689,-0.50053 0.82617,-0.98633v-5.5c-0.00026,-0.48953 -0.35484,-0.90693 -0.83789,-0.98633l-6.00781,-0.98242c-0.34266,-1.15945 -0.80206,-2.26356 -1.37109,-3.30664l3.50781,-4.99805c0.27882,-0.39749 0.23187,-0.93781 -0.11133,-1.28125l-3.89062,-3.88867c-0.34687,-0.34612 -0.89343,-0.38995 -1.29102,-0.10352l-4.92383,3.54102c-1.04908,-0.57636 -2.16255,-1.04318 -3.33398,-1.38867l-1.04687,-5.98437c-0.08364,-0.47917 -0.49991,-0.82867 -0.98633,-0.82812zM23.05664,4h3.80859l0.99609,5.68555c0.06772,0.38959 0.35862,0.70269 0.74219,0.79883c1.46251,0.36446 2.83609,0.94217 4.08984,1.70117c0.34265,0.20761 0.77613,0.1907 1.10156,-0.04297l4.67969,-3.36328l2.69336,2.69336l-3.33203,4.74805c-0.22737,0.3236 -0.24268,0.75079 -0.03906,1.08984c0.75149,1.25092 1.32146,2.61583 1.68555,4.07031c0.0969,0.38717 0.41473,0.67966 0.80859,0.74414l5.70703,0.93359v3.80859l-5.71875,1.00391c-0.3899,0.06902 -0.70237,0.36157 -0.79687,0.74609c-0.35988,1.45263 -0.93019,2.8175 -1.68164,4.06836c-0.20617,0.34256 -0.18851,0.775 0.04492,1.09961l3.37891,4.68945l-2.69336,2.69531l-4.74023,-3.32617c-0.32527,-0.22783 -0.75452,-0.24163 -1.09375,-0.03516c-1.24752,0.75899 -2.62251,1.33943 -4.08008,1.70898c-0.38168,0.09622 -0.67142,0.40737 -0.74023,0.79492l-1.00977,5.6875h-3.81445l-0.94141,-5.66211c-0.06549,-0.39365 -0.35874,-0.7107 -0.74609,-0.80664c-1.46338,-0.36069 -2.84314,-0.93754 -4.10547,-1.69531c-0.33857,-0.20276 -0.76473,-0.18746 -1.08789,0.03906l-4.70312,3.29492l-2.69531,-2.69922l3.32422,-4.64648c0.23221,-0.3254 0.24834,-0.75782 0.04102,-1.09961c-0.76602,-1.26575 -1.34535,-2.6454 -1.71094,-4.11523c-0.09555,-0.38244 -0.40684,-0.67307 -0.79492,-0.74219l-5.63086,-1v-3.81445l5.62695,-0.93555c0.39312,-0.06519 0.71002,-0.35754 0.80664,-0.74414c0.36873,-1.4749 0.94778,-2.85432 1.71094,-4.11719c0.20562,-0.33876 0.19183,-0.76697 -0.03516,-1.0918l-3.28516,-4.69531l2.69727,-2.69531l4.66211,3.33203c0.32413,0.23112 0.75447,0.248 1.0957,0.04297c1.25566,-0.75415 2.63862,-1.32636 4.10352,-1.68555c0.38927,-0.09584 0.68369,-0.41486 0.74805,-0.81055zM25,17c-4.40643,0 -8,3.59357 -8,8c0,4.40643 3.59357,8 8,8c4.40643,0 8,-3.59357 8,-8c0,-4.40643 -3.59357,-8 -8,-8zM25,19c3.32555,0 6,2.67445 6,6c0,3.32555 -2.67445,6 -6,6c-3.32555,0 -6,-2.67445 -6,-6c0,-3.32555 2.67445,-6 6,-6z"></path></g></g> </svg>',

        ],
    ];
@endphp
<!-- Sidebar -->
<div
    class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-20 bg-primary-md border-r border-gray-200 flex flex-col items-center py-4 px-2 gap-3 z-50 shadow-lg">
    {{-- Menu --}}
    @foreach ($menuItems as $item)
        @php
            // Check if current route matches this item or any of its submenu items
            $isActive = request()->routeIs($item['route'] ?? '');
            if (!empty($item['hasSubmenu'])) {
                foreach ($item['submenu'] as $sub) {
                    if (request()->routeIs($sub['route'] ?? '')) {
                        $isActive = true;
                        break;
                    }
                }
            }
        @endphp

        <div class="relative group w-full flex justify-center">
            <a href="{{ route($item['route']) }}" class="w-full flex justify-center">
                <button class="w-12 h-12 flex items-center justify-center rounded-xl transition-all duration-200 ease-out border border-transparent
                                        {{ $isActive
            ? 'bg-white text-primary-md shadow-md scale-110'
            : 'text-white hover:bg-white/10 hover:scale-105 active:scale-95' }}
                                        focus:outline-none focus:ring-2 focus:ring-white/20"
                    title="{{ $item['title'] ?? 'Menu Item' }}">
                    {!! $item['svg'] !!}
                </button>
            </a>

            {{-- Submenu --}}
            @if(!empty($item['hasSubmenu']))
                <div
                    class="absolute left-full hidden group-hover:flex flex-col bg-white border border-gray-200 rounded-lg shadow-xl z-50 py-1 animate-in fade-in slide-in-from-left-2 duration-150 min-w-max">
                    @foreach ($item['submenu'] as $sub)
                        <a href="{{ route($sub['route']) }}" class="flex items-center justify-start group text-gray-700 hover:bg-primary-md/10 hover:text-primary-md transition-all duration-200 ease-in-out
                                  text-sm font-medium h-10 px-3
                                  {{ request()->routeIs($sub['route'] ?? '') ? 'bg-primary-md/10 text-primary-md font-semibold' : '' }}
                                  {{ $loop->first ? 'rounded-t-lg' : '' }}
                                  {{ $loop->last ? 'rounded-b-lg' : '' }}">
                            <span class="w-full text-left group-hover:text-center transition-all duration-200">
                                {{ $sub['title'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                {{-- Tooltip for single items --}}
                <span class="absolute left-full ml-1 top-1/2 transform -translate-y-1/2 
                                                            z-20 bg-white text-black px-3 py-2 text-sm rounded shadow-md 
                                                            opacity-0 group-hover:opacity-100 
                                                            transition-all duration-500 ease-in-out 
                                                            translate-x-2 group-hover:translate-x-0
                                                            min-w-max whitespace-nowrap">
                    {{ __($item['title']) }}
                </span>
            @endif
        </div>
    @endforeach
</div>

<!-- End Sidebar -->
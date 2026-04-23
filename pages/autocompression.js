    	<!--Ajout pour autocompression Roy-->
		<!-- jQuery -->
    
	
	<script>
        $(document).ready(function() {

            $('input.companyid').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidforoem').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });

			$('input.Fld_Tag_Info_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidtaginfo').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.companyidtreacability').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
			$('input.Fld_Traceability_ID').typeahead({
                name: 'Fld_Company_Name',
				id: 'Fld_Company_ID',
                remote: 'list-company.php?query=%QUERY'
            });
        })
<!--Fin Ajout pour autocompression Roy-->
 </script>
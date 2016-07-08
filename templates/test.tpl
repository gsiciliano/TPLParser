<page backtop="14mm" backbottom="3mm" backleft="3mm" backright="3mm" style="font-size: 9pt">
    <page_header>
        TEST PAGE {$smarty.now}
    </page_header>
    
    <p>This is a <b>costant</b>: {$cont_1}</p>
    <br>
    
    <p>This is a <b>section:</b></p>
    <div style="padding-left:5mm">
    {section name=dataidx loop=$bodydataset}
        <p>{$bodydataset[dataidx].element}</p>
    {/section}
    </div>
</page>
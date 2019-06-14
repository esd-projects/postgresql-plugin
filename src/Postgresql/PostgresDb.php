<?php

namespace ESD\Plugins\Postgresql;
use PDO;


class PostgresDb extends \SeinopSys\PostgresDb
{
    /**
     * Allows passing any PDO object to the class, e.g. one initiated by a different library
     *
     * @param PDO $PDO
     *
     * @throws PDOException
     * @throws RuntimeException
     */
    public function setConnection(PDO $PDO)
    {
        $this->connection = $PDO;
        $this->setKeyWords();
    }

    /**
     * List of keywords used for escaping column names, automatically populated on connection
     *
     * @var string[]
     */
    public function setKeyWords()
    {
        if (empty($this->sqlKeywords)) {
            $this->sqlKeywords =  ["abort","absolute","access","action","add","admin","after","aggregate","all","also","alter","always","analyse","analyze","and","any","array","as","asc","assertion","assignment","asymmetric","at","attribute","authorization","backward","before","begin","between","bigint","binary","bit","boolean","both","by","cache","called","cascade","cascaded","case","cast","catalog","chain","char","character","characteristics","check","checkpoint","class","close","cluster","coalesce","collate","collation","column","comment","comments","commit","committed","concurrently","configuration","conflict","connection","constraint","constraints","content","continue","conversion","copy","cost","create","cross","csv","cube","current","current_catalog","current_date","current_role","current_schema","current_time","current_timestamp","current_user","cursor","cycle","data","database","day","deallocate","dec","decimal","declare","default","defaults","deferrable","deferred","definer","delete","delimiter","delimiters","depends","desc","dictionary","disable","discard","distinct","do","document","domain","double","drop","each","else","enable","encoding","encrypted","end","enum","escape","event","except","exclude","excluding","exclusive","execute","exists","explain","extension","external","extract","false","family","fetch","filter","first","float","following","for","force","foreign","forward","freeze","from","full","function","functions","global","grant","granted","greatest","group","grouping","handler","having","header","hold","hour","identity","if","ilike","immediate","immutable","implicit","import","in","including","increment","index","indexes","inherit","inherits","initially","inline","inner","inout","input","insensitive","insert","instead","int","integer","intersect","interval","into","invoker","is","isnull","isolation","join","key","label","language","large","last","lateral","leading","leakproof","least","left","level","like","limit","listen","load","local","localtime","localtimestamp","location","lock","locked","logged","mapping","match","materialized","maxvalue","method","minute","minvalue","mode","month","move","name","names","national","natural","nchar","next","no","none","not","nothing","notify","notnull","nowait","null","nullif","nulls","numeric","object","of","off","offset","oids","on","only","operator","option","options","or","order","ordinality","out","outer","over","overlaps","overlay","owned","owner","parallel","parser","partial","partition","passing","password","placing","plans","policy","position","preceding","precision","prepare","prepared","preserve","primary","prior","privileges","procedural","procedure","program","quote","range","read","real","reassign","recheck","recursive","ref","references","refresh","reindex","relative","release","rename","repeatable","replace","replica","reset","restart","restrict","returning","returns","revoke","right","role","rollback","rollup","row","rows","rule","savepoint","schema","scroll","search","second","security","select","sequence","sequences","serializable","server","session","session_user","set","setof","sets","share","show","similar","simple","skip","smallint","snapshot","some","sql","stable","standalone","start","statement","statistics","stdin","stdout","storage","strict","strip","substring","symmetric","sysid","system","table","tables","tablesample","tablespace","temp","template","temporary","text","then","time","timestamp","to","trailing","transaction","transform","treat","trigger","trim","true","truncate","trusted","type","types","unbounded","uncommitted","unencrypted","union","unique","unknown","unlisten","unlogged","until","update","user","using","vacuum","valid","validate","validator","value","values","varchar","variadic","varying","verbose","version","view","views","volatile","when","where","whitespace","window","with","within","without","work","wrapper","write","xml","xmlattributes","xmlconcat","xmlelement","xmlexists","xmlforest","xmlparse","xmlpi","xmlroot","xmlserialize","year","yes","zone"];
        }
    }

    public function isTransactionInProgress(): bool
    {
        return $this->_transaction_in_progress ?? false;
    }
}
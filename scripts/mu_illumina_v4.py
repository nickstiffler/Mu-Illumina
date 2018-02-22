from peewee import *

database = MySQLDatabase('mu_illumina_v4', **{'user': 'root', 'password': '420bigmoney'})

class UnknownField(object):
    def __init__(self, *_, **__): pass

class BaseModel(Model):
    class Meta:
        database = database

class ArabOrtholog(BaseModel):
    arabidopsis = CharField(index=True)
    evalue = FloatField()
    ortholog = CharField(index=True)

    class Meta:
        db_table = 'arab_ortholog'

class ArabPprs(BaseModel):
    arab = CharField(index=True, null=True)

    class Meta:
        db_table = 'arab_pprs'

class Arabidopsis(BaseModel):
    accession = CharField(index=True)
    defline = TextField(index=True)

    class Meta:
        db_table = 'arabidopsis'

class Barcode(BaseModel):
    barcode = CharField()
    date = IntegerField(db_column='date_id', index=True)

    class Meta:
        db_table = 'barcode'

class Candidates(BaseModel):
    cluster = IntegerField(db_column='cluster_id')
    comments = TextField()
    hidden = IntegerField()

    class Meta:
        db_table = 'candidates'

class Cluster(BaseModel):
    background = IntegerField(null=True)
    barcode = IntegerField(db_column='barcode_id', index=True)
    chr = CharField(index=True)
    end = IntegerField(index=True)
    identified_gene = CharField(null=True)
    insert_loc = CharField()
    insertion_end = IntegerField()
    insertion_start = IntegerField()
    interesting = IntegerField()
    maize = CharField(null=True)
    primer = TextField(null=True)
    seq = TextField()
    size = IntegerField(index=True)
    start = IntegerField(index=True)

    class Meta:
        db_table = 'cluster'

class CpProteins(BaseModel):
    arab = CharField()
    maize = CharField()
    rice = CharField()

    class Meta:
        db_table = 'cp_proteins'

class Domains(BaseModel):
    accession = CharField()
    description = CharField()
    gene = CharField(index=True)

    class Meta:
        db_table = 'domains'
        indexes = (
            (('accession', 'description'), False),
        )

class Ear(BaseModel):
    barcode = IntegerField(db_column='barcode_id', index=True)
    ear = CharField(index=True)
    mutant = IntegerField(db_column='mutant_id')

    class Meta:
        db_table = 'ear'

class EarAnnotation(BaseModel):
    coi = IntegerField(db_column='coi_id', null=True)
    comments = CharField(null=True)
    ear = IntegerField(db_column='ear_id', null=True)
    heritable = IntegerField(null=True)
    homozygous = IntegerField(null=True)
    maize = CharField(null=True)
    pcr_confirmed = IntegerField(null=True)

    class Meta:
        db_table = 'ear_annotation'

class InterestingGenes(BaseModel):
    gene_name = CharField()
    maize = CharField()

    class Meta:
        db_table = 'interesting_genes'

class Maize3(BaseModel):
    chr = CharField(null=True)
    cluster = IntegerField(db_column='cluster_id', index=True)
    end = IntegerField(null=True)
    id = IntegerField()
    insertion_end = IntegerField(null=True)
    insertion_start = IntegerField(null=True)
    start = IntegerField(null=True)

    class Meta:
        db_table = 'maize3'
        indexes = (
            (('id', 'cluster'), True),
        )
        primary_key = CompositeKey('cluster', 'id')

class MassSpec(BaseModel):
    arab = CharField()
    maize = CharField()
    rice = CharField()

    class Meta:
        db_table = 'mass_spec'

class Meta(BaseModel):
    cluster = IntegerField(db_column='cluster_id')
    name = CharField()
    value = CharField()

    class Meta:
        db_table = 'meta'

class Mutant(BaseModel):
    ear = IntegerField(db_column='ear_id')
    name = CharField(index=True)

    class Meta:
        db_table = 'mutant'

class Orders(BaseModel):
    coop_date = CharField()
    ear = CharField()
    ear_sent = CharField()
    email = CharField()
    lab = CharField()
    maize = CharField()
    position = CharField()
    primers = TextField()
    pursue = CharField()
    request_date = CharField()
    requester = CharField()
    sent_date = CharField()

    class Meta:
        db_table = 'orders'

class Ortholog(BaseModel):
    accession = CharField(index=True)
    annotation = CharField()
    cluster = IntegerField(db_column='cluster_id', index=True)
    evalue = FloatField()
    link = CharField()
    organism = CharField()

    class Meta:
        db_table = 'ortholog'

class PtypePprs(BaseModel):
    maize = CharField(index=True, null=True)

    class Meta:
        db_table = 'ptype_pprs'

class Public(BaseModel):
    arab = CharField()
    arab_desc = TextField(index=True)
    cluster = IntegerField(db_column='cluster_id')
    insert_pos = CharField()
    maize = CharField()
    rice = CharField()

    class Meta:
        db_table = 'public'

class Rice7(BaseModel):
    accession = CharField(index=True, null=True)
    cluster = IntegerField(db_column='cluster_id', index=True, null=True)
    evalue = FloatField(null=True)

    class Meta:
        db_table = 'rice7'

class RunDate(BaseModel):
    date = DateField()
    lane = IntegerField()
    name = CharField()
    public = IntegerField()

    class Meta:
        db_table = 'run_date'

class Target(BaseModel):
    gene = CharField(index=True)
    method = CharField()
    prediction = CharField(index=True)

    class Meta:
        db_table = 'target'

class TransFeature(BaseModel):
    end = IntegerField()
    start = IntegerField()
    trans = IntegerField(db_column='trans_id')
    type = CharField()

    class Meta:
        db_table = 'trans_feature'

class GeneCoords(BaseModel):
    accession = CharField()
    chr = CharField(index=True)
    end = IntegerField(index=True)
    start = IntegerField(index=True)
    strand = CharField()

    class Meta:
        db_table = 'gene_coords'

class MaizeGene(BaseModel):
	accession = CharField()
	cluster_id = IntegerField()

	class Meta:
		db_table = 'maize_gene'


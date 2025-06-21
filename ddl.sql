-- Usuários
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password TEXT NOT NULL
);

-- Livros
CREATE TABLE books (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    abbreviation VARCHAR(20),
    author VARCHAR(255),
    description TEXT
);

-- Biblioteca do usuário (quais livros ele adicionou)
CREATE TABLE libraries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    book_id INTEGER NOT NULL REFERENCES books(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Páginas (para livros paginados)
CREATE TABLE pages (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    book_id INTEGER NOT NULL REFERENCES books(id) ON DELETE CASCADE
);

-- Capítulos
CREATE TABLE chapters (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    book_id INTEGER NOT NULL REFERENCES books(id) ON DELETE CASCADE
);

-- Versículos
CREATE TABLE verses (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    chapter_id INTEGER NOT NULL REFERENCES chapters(id) ON DELETE CASCADE
);

-- Notas
CREATE TABLE notes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    book_id INTEGER NOT NULL REFERENCES books(id) ON DELETE CASCADE,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Relacionamento: notas <-> páginas
CREATE TABLE note_pages (
    id SERIAL PRIMARY KEY,
    page_id INTEGER NOT NULL REFERENCES pages(id) ON DELETE CASCADE,
    note_id INTEGER NOT NULL REFERENCES notes(id) ON DELETE CASCADE
);

-- Relacionamento: notas <-> capítulos
CREATE TABLE note_chapters (
    id SERIAL PRIMARY KEY,
    chapter_id INTEGER NOT NULL REFERENCES chapters(id) ON DELETE CASCADE,
    note_id INTEGER NOT NULL REFERENCES notes(id) ON DELETE CASCADE
);

-- Relacionamento: notas <-> versículos
CREATE TABLE note_verses (
    id SERIAL PRIMARY KEY,
    verse_id INTEGER NOT NULL REFERENCES verses(id) ON DELETE CASCADE,
    note_id INTEGER NOT NULL REFERENCES notes(id) ON DELETE CASCADE
);

-- Anotações adicionadas por outros usuários em páginas
CREATE TABLE note_pages_added (
    id SERIAL PRIMARY KEY,
    page_id INTEGER NOT NULL REFERENCES pages(id) ON DELETE CASCADE,
    note_id_added INTEGER NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

-- Anotações adicionadas por outros usuários em capítulos
CREATE TABLE note_chapters_added (
    id SERIAL PRIMARY KEY,
    chapter_id INTEGER NOT NULL REFERENCES chapters(id) ON DELETE CASCADE,
    note_id_added INTEGER NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

-- Anotações adicionadas por outros usuários em versículos
CREATE TABLE note_verses_added (
    id SERIAL PRIMARY KEY,
    verse_id INTEGER NOT NULL REFERENCES verses(id) ON DELETE CASCADE,
    note_id_added INTEGER NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO books (name, abbreviation, author, description)
VALUES (
    'Filipenses',
    'fp',
    'Apóstolo Paulo',
    'Carta de Paulo aos filipenses, escrita da prisão, destacando a alegria em Cristo e a perseverança na fé.'
);

INSERT INTO chapters (content, book_id) VALUES ('1', 1);
INSERT INTO chapters (content, book_id) VALUES ('2', 1);
INSERT INTO chapters (content, book_id) VALUES ('3', 1);
INSERT INTO chapters (content, book_id) VALUES ('4', 1);

select * from chapters

delete from verses 

INSERT INTO verses (content, chapter_id) VALUES 
('Paulo e Timóteo, servos de Cristo Jesus, a todos os santos em Cristo Jesus que estão em Filipos, juntamente com os bispos e diáconos:', 1),
('A vocês, graça e paz da parte de Deus nosso Pai e do Senhor Jesus Cristo.', 1),
('Agradeço a meu Deus toda vez que me lembro de vocês.', 1),
('Em todas as minhas orações em favor de vocês, sempre oro com alegria', 1),
('por causa da cooperação que vocês têm dado ao evangelho, desde o primeiro dia até agora.', 1),
('Estou convencido de que aquele que começou boa obra em vocês, vai completá-la até o dia de Cristo Jesus.', 1),
('É justo que eu assim me sinta a respeito de todos vocês, uma vez que os tenho em meu coração, pois, quer nas correntes que me prendem quer defendendo e confirmando o evangelho, todos vocês participam comigo da graça de Deus.', 1),
('Deus é minha testemunha de como tenho saudade de todos vocês, com a profunda afeição de Cristo Jesus.', 1),
('Esta é a minha oração: que o amor de vocês aumente cada vez mais em conhecimento e em toda a percepção,', 1),
('para discernirem o que é melhor, a fim de serem puros e irrepreensíveis até o dia de Cristo,', 1),
('cheios do fruto da justiça, fruto que vem por meio de Jesus Cristo, para glória e louvor de Deus.', 1),
('Quero que saibam, irmãos, que aquilo que me aconteceu tem antes servido para o progresso do evangelho.', 1),
('Como resultado, tornou-se evidente a toda a guarda do palácio e a todos os demais que estou na prisão por causa de Cristo.', 1),
('E a maioria dos irmãos, motivados no Senhor pela minha prisão, estão anunciando a palavra com maior determinação e destemor.', 1),
('É verdade que alguns pregam a Cristo por inveja e rivalidade, mas outros o fazem de boa vontade.', 1),
('Estes o fazem por amor, sabendo que aqui me encontro para a defesa do evangelho.', 1),
('Aqueles pregam a Cristo por ambição egoísta, sem sinceridade, pensando que me podem causar sofrimento enquanto estou preso.', 1),
('Mas, que importa? O importante é que de qualquer forma, seja por motivos falsos ou verdadeiros, Cristo está sendo pregado, e por isso me alegro. De fato, continuarei a alegrar-me,', 1),
('pois sei que o que me aconteceu resultará em minha libertação, graças às orações de vocês e ao auxílio do Espírito de Jesus Cristo.', 1),
('Aguardo ansiosamente e espero que em nada serei envergonhado. Pelo contrário, com toda a determinação de sempre, também agora Cristo será engrandecido em meu corpo, quer pela vida quer pela morte;', 1),
('porque para mim o viver é Cristo e o morrer é lucro.', 1),
('Caso continue vivendo no corpo, terei fruto do meu trabalho. E já não sei o que escolher!', 1),
('Estou pressionado dos dois lados: desejo partir e estar com Cristo, o que é muito melhor;', 1),
('contudo, é mais necessário, por causa de vocês, que eu permaneça no corpo.', 1),
('Convencido disso, sei que vou permanecer e continuar com todos vocês, para o seu progresso e alegria na fé,', 1),
('a fim de que, pela minha presença, outra vez a exultação de vocês em Cristo Jesus transborde por minha causa.', 1),
('Não importa o que aconteça, exerçam a sua cidadania de maneira digna do evangelho de Cristo, para que assim, quer eu vá e os veja, quer apenas ouça a seu respeito em minha ausência, fique eu sabendo que vocês permanecem firmes num só espírito, lutando unânimes pela fé evangélica,', 1),
('sem de forma alguma deixar-se intimidar por aqueles que se opõem a vocês. Para eles isso é sinal de destruição, mas para vocês de salvação, e isso da parte de Deus;', 1),
('pois a vocês foi dado o privilégio de, não apenas crer em Cristo, mas também de sofrer por ele,', 1),
('já que estão passando pelo mesmo combate que me viram enfrentar e agora ouvem que ainda enfrento.', 1);

INSERT INTO verses (content, chapter_id) VALUES 
('Se por estarmos em Cristo, nós temos alguma motivação, alguma exortação de amor, alguma comunhão no Espírito, alguma profunda afeição e compaixão,', 2),
('completem a minha alegria, tendo o mesmo modo de pensar, o mesmo amor, um só espírito e uma só atitude.', 2),
('Nada façam por ambição egoísta ou por vaidade, mas humildemente considerem os outros superiores a si mesmos.', 2),
('Cada um cuide, não somente dos seus interesses, mas também dos interesses dos outros.', 2),
('Seja a atitude de vocês a mesma de Cristo Jesus,', 2),
('que, embora sendo Deus, não considerou que o ser igual a Deus era algo a que devia apegar-se;', 2),
('mas esvaziou-se a si mesmo, vindo a ser servo, tornando-se semelhante aos homens.', 2),
('E, sendo encontrado em forma humana, humilhou-se a si mesmo e foi obediente até à morte, e morte de cruz!', 2),
('Por isso Deus o exaltou à mais alta posição e lhe deu o nome que está acima de todo nome,', 2),
('para que ao nome de Jesus se dobre todo joelho, no céu, na terra e debaixo da terra,', 2),
('e toda língua confesse que Jesus Cristo é o Senhor, para a glória de Deus Pai.', 2),
('Assim, meus amados, como sempre vocês obedeceram, não apenas em minha presença, porém muito mais agora na minha ausência, ponham em ação a salvação de vocês com temor e tremor,', 2),
('pois é Deus quem efetua em vocês tanto o querer quanto o realizar, de acordo com a boa vontade dele.', 2),
('Façam tudo sem queixas nem discussões,', 2),
('para que venham a tornar-se puros e irrepreensíveis, filhos de Deus inculpáveis no meio de uma geração corrompida e depravada, na qual vocês brilham como estrelas no universo,', 2),
('retendo firmemente a palavra da vida. Assim, no dia de Cristo eu me orgulharei de não ter corrido nem me esforçado inutilmente.', 2),
('Contudo, mesmo que eu esteja sendo derramado como oferta de bebida sobre o serviço que provém da fé que vocês têm, o sacrifício que oferecem a Deus, estou alegre e me regozijo com todos vocês.', 2),
('Estejam vocês também alegres, e regozijem-se comigo.', 2),
('Espero no Senhor Jesus enviar-lhes Timóteo brevemente, para que eu também me sinta animado quando receber notícias de vocês.', 2),
('Não tenho ninguém como ele, que tenha interesse sincero pelo bem-estar de vocês,', 2),
('pois todos buscam os seus próprios interesses e não os de Jesus Cristo.', 2),
('Mas vocês sabem que Timóteo foi aprovado, porque serviu comigo no trabalho do evangelho como um filho ao lado de seu pai.', 2),
('Portanto, é ele quem espero enviar, tão logo me certifique da minha situação,', 2),
('confiando no Senhor que em breve também poderei ir.', 2),
('Contudo, penso que será necessário enviar-lhes de volta Epafrodito, meu irmão, cooperador e companheiro de lutas, mensageiro que vocês enviaram para atender às minhas necessidades.', 2),
('Pois ele tem saudade de todos vocês e está angustiado porque ficaram sabendo que ele esteve doente.', 2),
('De fato, ficou doente e quase morreu. Mas Deus teve misericórdia dele, e não somente dele, mas também de mim, para que eu não tivesse tristeza sobre tristeza.', 2),
('Por isso, logo o enviarei, para que, quando o virem novamente, fiquem alegres e eu tenha menos tristeza.', 2),
('E peço que vocês o recebam no Senhor com grande alegria e honrem a homens como este,', 2),
('porque ele quase morreu por amor à causa de Cristo, arriscando a vida para suprir a ajuda que vocês não me podiam dar.', 2);

INSERT INTO verses (content, chapter_id) VALUES 
('Finalmente, meus irmãos, alegrem-se no Senhor! Escrever-lhes de novo as mesmas coisas não é cansativo para mim e é uma segurança para vocês.', 4),
('Cuidado com os cães, cuidado com esses que praticam o mal, cuidado com a falsa circuncisão!', 4),
('Pois nós é que somos a circuncisão, nós que adoramos pelo Espírito de Deus, que nos gloriamos em Cristo Jesus e não temos confiança alguma na carne,', 4),
('embora eu mesmo tivesse razões para ter tal confiança. Se alguém pensa que tem razões para confiar na carne, eu ainda mais:', 4),
('circuncidado no oitavo dia de vida, pertencente ao povo de Israel, à tribo de Benjamim, verdadeiro hebreu; quanto à lei, fariseu;', 4),
('quanto ao zelo, perseguidor da igreja; quanto à justiça que há na lei, irrepreensível.', 4),
('Mas o que para mim era lucro, passei a considerar perda, por causa de Cristo.', 4),
('Mais do que isso, considero tudo como perda, comparado com a suprema grandeza do conhecimento de Cristo Jesus, meu Senhor, por cuja causa perdi todas as coisas. Eu as considero como esterco para poder ganhar a Cristo', 4),
('e ser encontrado nele, não tendo a minha própria justiça que procede da lei, mas a que vem mediante a fé em Cristo, a justiça que procede de Deus e se baseia na fé.', 4),
('Quero conhecer a Cristo, ao poder da sua ressurreição e à participação em seus sofrimentos, tornando-me como ele em sua morte', 4),
('para, de alguma forma, alcançar a ressurreição dentre os mortos.', 4),
('Não que eu já tenha obtido tudo isso ou tenha sido aperfeiçoado, mas prossigo para alcançá-lo, pois para isso também fui alcançado por Cristo Jesus.', 4),
('Irmãos, não penso que eu mesmo já o tenha alcançado, mas uma coisa faço: esquecendo-me das coisas que ficaram para trás e avançando para as que estão adiante,', 4),
('prossigo para o alvo, a fim de ganhar o prêmio do chamado celestial de Deus em Cristo Jesus.', 4),
('Todos nós que alcançamos a maturidade devemos ver as coisas dessa forma, e se em algum aspecto vocês pensam de modo diferente, isso também Deus lhes esclarecerá.', 4),
('Tão-somente vivamos de acordo com o que já alcançamos.', 4),
('Irmãos, sigam unidos o meu exemplo e observem os que vivem de acordo com o padrão que lhes apresentamos.', 4),
('Pois, como já lhes disse repetidas vezes, e agora repito com lágrimas, há muitos que vivem como inimigos da cruz de Cristo.', 4),
('Quanto a estes, o seu destino é a perdição, o seu deus é o estômago e têm orgulho do que é vergonhoso; eles só pensam nas coisas terrenas.', 4),
('A nossa cidadania, porém, está nos céus, de onde esperamos ansiosamente um Salvador, o Senhor Jesus Cristo.', 4),
('Pelo poder que o capacita a colocar todas as coisas debaixo do seu domínio, ele transformará os nossos corpos humilhados, para serem semelhantes ao seu corpo glorioso.', 4);

INSERT INTO verses (content, chapter_id) VALUES 
('Portanto, meus irmãos, a quem amo e de quem tenho saudade, vocês que são a minha alegria e a minha coroa, permaneçam assim firmes no Senhor, ó amados!', 5),
('O que eu rogo a Evódia e também a Síntique é que vivam em harmonia no Senhor.', 5),
('Sim, e peço a você, leal companheiro de jugo, que as ajude; pois lutaram ao meu lado na causa do evangelho, com Clemente e meus demais cooperadores. Os seus nomes estão no livro da vida.', 5),
('Alegrem-se sempre no Senhor. Novamente direi: alegrem-se!', 5),
('Seja a amabilidade de vocês conhecida por todos. Perto está o Senhor.', 5),
('Não andem ansiosos por coisa alguma, mas em tudo, pela oração e súplicas, e com ação de graças, apresentem seus pedidos a Deus.', 5),
('E a paz de Deus, que excede todo o entendimento, guardará os seus corações e as suas mentes em Cristo Jesus.', 5),
('Finalmente, irmãos, tudo o que for verdadeiro, tudo o que for nobre, tudo o que for correto, tudo o que for puro, tudo o que for amável, tudo o que for de boa fama, se houver algo de excelente ou digno de louvor, pensem nessas coisas.', 5),
('Tudo o que vocês aprenderam, receberam, ouviram e viram em mim, ponham-no em prática. E o Deus da paz estará com vocês.', 5),
('Alegro-me grandemente no Senhor, porque finalmente vocês renovaram o seu interesse por mim. De fato, vocês já se interessavam, mas não tinham oportunidade para demonstrá-lo.', 5),
('Não estou dizendo isso porque esteja necessitado, pois aprendi a adaptar-me a toda e qualquer circunstância.', 5),
('Sei o que é passar necessidade e sei o que é ter fartura. Aprendi o segredo de viver contente em toda e qualquer situação, seja bem alimentado, seja com fome, tendo muito, ou passando necessidade.', 5),
('Tudo posso naquele que me fortalece.', 5),
('Apesar disso, vocês fizeram bem em participar de minhas tribulações.', 5),
('Como vocês sabem, filipenses, nos seus primeiros dias no evangelho, quando parti da Macedônia, nenhuma igreja partilhou comigo no que se refere a dar e receber, exceto vocês;', 5),
('pois, estando eu em Tessalônica, vocês me mandaram ajuda, não apenas uma vez, mas duas, quando tive necessidade.', 5),
('Não que eu esteja procurando ofertas, mas o que pode ser creditado na conta de vocês.', 5),
('Recebi tudo, e o que tenho é mais que suficiente. Estou amplamente suprido, agora que recebi de Epafrodito os donativos que vocês enviaram. Elas são uma oferta de aroma suave, um sacrifício aceitável e agradável a Deus.', 5),
('O meu Deus suprirá todas as necessidades de vocês, de acordo com as suas gloriosas riquezas em Cristo Jesus.', 5),
('A nosso Deus e Pai seja a glória para todo o sempre. Amém.', 5),
('Saúdem a todos os santos em Cristo Jesus. Os irmãos que estão comigo enviam saudações.', 5),
('Todos os santos lhes enviam saudações, especialmente os que estão no palácio de César.', 5),
('A graça do Senhor Jesus Cristo seja com o espírito de vocês. Amém.', 5);

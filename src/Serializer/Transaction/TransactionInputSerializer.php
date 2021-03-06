<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Serializer\Transaction;

use BitWasp\Bitcoin\Script\Script;
use BitWasp\Bitcoin\Serializer\Types;
use BitWasp\Bitcoin\Transaction\TransactionInput;
use BitWasp\Bitcoin\Transaction\TransactionInputInterface;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\BufferInterface;
use BitWasp\Buffertools\Parser;

class TransactionInputSerializer
{
    /**
     * @var OutPointSerializer
     */
    private $outpointSerializer;

    /**
     * @var \BitWasp\Buffertools\Types\VarString
     */
    private $varstring;

    /**
     * @var \BitWasp\Buffertools\Types\Uint32
     */
    private $uint32le;

    /**
     * TransactionInputSerializer constructor.
     * @param OutPointSerializerInterface $outPointSerializer
     */
    public function __construct(OutPointSerializerInterface $outPointSerializer)
    {
        $this->outpointSerializer = $outPointSerializer;
        $this->varstring = Types::varstring();
        $this->uint32le = Types::uint32le();
    }

    /**
     * @param TransactionInputInterface $input
     * @return BufferInterface
     */
    public function serialize(TransactionInputInterface $input): BufferInterface
    {
        return new Buffer(
            $this->outpointSerializer->serialize($input->getOutPoint())->getBinary() .
            $this->varstring->write($input->getScript()->getBuffer()) .
            $this->uint32le->write($input->getSequence())
        );
    }

    /**
     * @param Parser $parser
     * @return TransactionInputInterface
     * @throws \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     * @throws \Exception
     */
    public function fromParser(Parser $parser): TransactionInputInterface
    {
        return new TransactionInput(
            $this->outpointSerializer->fromParser($parser),
            new Script($this->varstring->read($parser)),
            (int) $this->uint32le->read($parser)
        );
    }

    /**
     * @param BufferInterface $string
     * @return TransactionInput
     * @throws \BitWasp\Buffertools\Exceptions\ParserOutOfRange
     * @throws \Exception
     */
    public function parse(BufferInterface $string): TransactionInput
    {
        return $this->fromParser(new Parser($string));
    }
}
